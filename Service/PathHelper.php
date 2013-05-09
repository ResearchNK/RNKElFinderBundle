<?php

namespace RNK\ElFinderBundle\Service;

use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\Helper\CoreAssetsHelper;
use Symfony\Component\HttpKernel\Log\LoggerInterface;

class PathHelper
{

  protected $root_dir;
  protected $assets_helper;
  protected $request;
  protected $logger;
  protected $roots;


  public function __construct(
    $root_dir,
    CoreAssetsHelper $assets_helper,
    Request $request,
    LoggerInterface $logger,
    array $options)
  {
    $this->root_dir = $root_dir;
    $this->roots = $options['connector']['roots'];
    $this->assets_helper = $assets_helper;
    $this->logger = $logger;
    $this->request = $request;
  }

  public function generateAbsoluteFileUrl($file_path = '')
  {
    $web_path = $this->assets_helper->getUrl($file_path);
    return $this->request->getSchemeAndHttpHost()
            . str_replace('\\', '/', $web_path);
  }

  public function getAbsoluteDirForNode($file_path = '')
  {
    return $this->absolute_dir = $this->root_dir . '/../web/' . $file_path;
  }

  public function getFilesList($root_name = null)
  {
    $root = $this->getRootByName($root_name, true);
    $root_path = $root['path'];

    static $files = null;
    if($files == null)
    {
      $files = array();
      $finder = new Finder();
      try
      {
        $finder->files()->in($this->getAbsoluteDirForNode($root_path));
      }
      catch(\InvalidArgumentException $e)
      {
        $this->logger->warn($e->getMessage());
        return array();
      }

      foreach($finder as $file)
      {
        $files[] = $this->generateAbsoluteFileUrl(
                $root_path . '/' . $file->getRelativePathname());
      }
      $files = array_combine($files, $files);
    }
    return $files;
  }

  /**
   * Gets the default root (first one).
   * @return array root configuration
   */
  protected function getDefaultRoot()
  {
    return reset($this->roots);
  }
  
  protected function getRootByName($root_name, $use_default_if_not_exists = false)
  {
    if(!$root_name || !isset($this->roots[$root_name]))
    {
      if(!$use_default_if_not_exists)
      {
        throw new \InvalidArgumentException('Root "' . $root_name . '" does not exists.');
      }
      else
      {
         $root = $this->getDefaultRoot();
      }
    }
    else
    {
      $root = $this->roots[$root_name];
    }
    return $root;
  }

}
