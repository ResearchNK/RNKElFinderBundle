<?php

namespace RNK\ElFinderBundle\Exception;
/**
 * Description of ElFinderException
 *
 * @author mzakarczemny
 */
class ElFinderException extends \RuntimeException
{
  public $el_finder_errors = array();

  public static function create(array $errors) {
   $exception = new self('['.join('][', $errors).']');
   $exception->el_finder_errors = $errors;
   return $exception;
  }
}