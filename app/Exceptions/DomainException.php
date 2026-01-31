<?php

namespace App\Exceptions;

use Exception;

/**
 * Base class for all domain/business exceptions.
 * 
 * Makes it easy to handle all domain exceptions in one place.
 */
abstract class DomainException extends Exception
{
    
}