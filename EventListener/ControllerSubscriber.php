<?php

namespace Isometriks\Bundle\GoogleApiBundle\EventListener;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Util\ClassUtils;
use Isometriks\Bundle\GoogleApiBundle\Controller\RedirectController;
use Isometriks\Bundle\GoogleApiBundle\Google\ScopeManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

class ControllerSubscriber implements EventSubscriberInterface
{
    protected $reader;
    protected $scopeManager;
    protected $annotationClass = 'Isometriks\\Bundle\\GoogleApiBundle\\Annotation\\GoogleScope';

    public function __construct(Reader $reader, ScopeManager $scopeManager)
    {
        $this->reader = $reader;
        $this->scopeManager = $scopeManager;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        if (!is_array($controller = $event->getController())) {
            return;
        }

        $className = class_exists('Doctrine\Common\Util\ClassUtils') ? ClassUtils::getClass($controller[0]) : get_class($controller[0]);
        $object = new \ReflectionClass($className);
        $method = $object->getMethod($controller[1]);

        $classAnnotations = $this->reader->getClassAnnotations($object);
        $methodAnnotations = $this->reader->getMethodAnnotations($method);

        $requiredScopes = array();

        foreach (array_merge($classAnnotations, $methodAnnotations) as $annotation) {
            if ($annotation instanceof $this->annotationClass) {
                if (!$this->scopeManager->hasScopes($annotation->getScopes())) {
                    $requiredScopes = array_merge($requiredScopes, $annotation->getScopes());
                }
            }
        }

        if (count($scopes = $requiredScopes) > 0) {
            $this->obtainScopes($scopes, $event);
        }
    }

    protected function obtainScopes(array $scopes, FilterControllerEvent $event)
    {
        $request = $event->getRequest();

        $url =  $this->scopeManager->obtainScopeUrl(
            $scopes,
            $request->getRequestUri()
        );

        // Set request parameter for redirect
        $request->attributes->set('url', '/'.ltrim($url, '/'));

        // Set controller to redirect controller instead
        $event->setController(array(new RedirectController(), 'redirectAction'));
    }

    public static function getSubscribedEvents()
    {
        return array(
            'kernel.controller' => 'onKernelController',
        );
    }
}
