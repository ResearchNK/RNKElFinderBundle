<?php

namespace RNK\ElFinderBundle\Service;

require_once __DIR__.'/../elFinder/elFinderVolumeDriver.class.php';
require_once __DIR__.'/../elFinder/elFinderVolumeLocalFileSystem.class.php';
require_once __DIR__.'/../elFinder/elFinder.class.php';
require_once __DIR__.'/../elFinder/elFinderConnector.class.php';

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\FileBag;
use RNK\ElFinderBundle\Exception\ElFinderException;

/**
 * @DI\Service("rnk_el_finder.connector", scope="request")
 */
class ElFinderConnector
{
  protected $path_helper;
  protected $request;
  protected $options;
  protected $elFinder;
  const driver = 'LocalFileSystem';

  /**
    * @DI\InjectParams({
    *    "path_helper" = @DI\Inject("rnk_el_finder.path_helper"),
    *    "request" = @DI\Inject("request"),
    *    "options" = @DI\Inject("%rnk_el_finder%")
    * })
    */
  public function __construct( $path_helper, Request $request, array $options)
  {
    $this->path_helper = $path_helper;
    $this->request = $request;
    $this->options = $options;
    $this->options = $this->configure($options['connector']);
  }

  /**
   * @return array
   */
  protected function configure(array $parameters)
  {
      $options = array();
      $options['debug'] = $parameters['debug'];
      $options['roots'] = array();

      foreach ($parameters['roots'] as $parameter) {
          $path = $parameter['path'];
          
          if(isset($parameter['show_hidden_files']) && $parameter['show_hidden_files'])
            $show_hidden_files = true;
          else
            $show_hidden_files = false;

          $options['roots'][] = array(
              'driver'        => $parameter['driver'],
              'path'          => $path,
              'URL'           => $this->path_helper->generateAbsoluteFileUrl($path),
              'accessControl' => $show_hidden_files ? null : array($this, 'access'),
              'uploadAllow'   => $parameter['upload_allow'],
              'uploadDeny'    => $parameter['upload_deny'],
              'uploadMaxSize' => $parameter['upload_max_size']
          );
      }

      return $options;
  }
    
  /**
   * @return Response
   */
  public function connect() {
    error_reporting(0);
    $this->elFinder = new \elFinder($this->options);
    $data = $this->processRequest();
    return $this->processOutput($data);
  }

  /**
   * Based on elFinderConnector::run
   **/
  public function processRequest()
  {
    $command = $this->request->get('cmd');
    if(!$this->elFinder->loaded())
    {
      throw ElFinderException::create(array(
          elFinder::ERROR_CONF,
          elFinder::ERROR_CONF_NO_VOL
          ));
    }

    // telepat_mode: on
    if(!$command && $this->request->isMethod('post'))
    {
      throw ElFinderException::create(array(
          elFinder::ERROR_UPLOAD,
          elFinder::ERROR_UPLOAD_TOTAL_SIZE,
          ));
    }
    // telepat_mode: off

    if(!$this->elFinder->commandExists($command))
    {
      throw ElFinderException::create(array(
          elFinder::ERROR_UNKNOWN_CMD,
          ));
    }

    // collect required arguments to exec command
    $args = array();
    foreach($this->elFinder->commandArgsList($command) as $name => $is_required)
    {
      $arg = null;
      if($name == 'FILES')
      {
        $arg = $this->fileBagToArray($this->request->files);
      }
      else
      {
        $arg = $this->request->get($name);
        if($is_required && $arg == null)
        {
          throw ElFinderException::create(array(
              elFinder::ERROR_INV_PARAMS,
              $command
              ));
        }
        if(!is_array($arg))
          $arg = trim($arg);
      }

      $args[$name] = $arg;
    }

    $args['debug'] = (boolean) $this->request->get('debug', false);
    return $this->elFinder->exec($command, $args);
  }

  /**
   * Based on elFinderConnector::output
   * Create response object. Sends headers immediately.
   * If pointer exists in data do some magic stuff with files.
   *
   * @param  array data to output
   * @todo Refactor headers using $response->headers->set();
   * @return Symfony\Component\HttpFoundation\Response
   */
  protected function processOutput(array $data)
  {
    $response = new Response();

    $headers = isset($data['header']) ? $data['header'] : false;
    $this->sendHeaders($headers);
    unset($data['header']);

    if(isset($data['pointer']))
    {
      rewind($data['pointer']);
      fpassthru($data['pointer']);
      if(!empty($data['volume']))
      {
        $data['volume']->close($data['pointer'], $data['info']['hash']);
      }
    }
    else
    {
      if(!empty($data['raw']) && !empty($data['error']))
      {
        $response->setContent($data['error']);
      }
      else
      {
        $response->setContent(json_encode($data));
      }
    }
    return $response;
  }

  /**
   * Transform FileBag to file array in php native format
   * @param \Symfony\Component\HttpFoundation\FileBag $file_bag
   * @return array
   */
  protected function fileBagToArray(FileBag $file_bag) {
    $files = $file_bag->all();
    $uploaded_files = $files['upload'];
    $files_array = array(
          'name'      => array(),
          'type'      => array(),
          'tmp_name'  => array(),
          'error'     => array(),
          'size'      => array(),
          );

    foreach($uploaded_files as $file) {
      $files_array['name'][] = $file->getClientOriginalName();
      $files_array['type'][] = $file->getMimeType();
      $files_array['tmp_name'][] = $file->getPathName();
      $files_array['error'][] = $file->getError();
      $files_array['size'][] = $file->getClientSize();
    }
    return array('upload' => $files_array);
  }
  
  /**
   * Based on elFinderConnector::output
   * Should be removed after processOutput refactoring
   * @param string $headers
   */
  protected function sendHeaders($headers) {
    if(!$headers) {
      $headers = array('Content-Type: application/json');
    }
    if(is_array($header))
    {
      foreach($header as $h)
      {
        header($h);
      }
    }
    else
    {
      header($header);
    }
  }

  /**
   * Simple function to demonstrate how to control file access using "accessControl" callback.
   * This method will disable accessing files/folders starting from '.' (dot)
   *
   * @param  string  $attr  attribute name (read|write|locked|hidden)
   * @param  string  $path  file path relative to volume root directory started with directory separator
   * @return bool|null
   **/
  public function access($attr, $path, $data, $volume) {
              return strpos(basename($path), '.') === 0       // if file/folder begins with '.' (dot)
              ? !($attr == 'read' || $attr == 'write')    // set read+write to false, other (locked+hidden) set to true
              :  null;                                    // else elFinder decide it itself
  }
}