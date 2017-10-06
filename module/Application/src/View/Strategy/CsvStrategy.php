<?php
namespace Application\View\Strategy;

use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\View\Model;
use Zend\View\ViewEvent;
use Application\View\Renderer\CsvRenderer;
use Application\View\Model\CsvModel;

class CsvStrategy extends AbstractListenerAggregate
{

    protected $renderer;
    protected $listeners = array();

    public function __construct(CsvRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    public function attach(EventManagerInterface $events, $priority = 1)
    {
       $this->listeners[] = $events->attach(ViewEvent::EVENT_RENDERER, array($this, 'selectRenderer'), $priority);
       $this->listeners[] = $events->attach(ViewEvent::EVENT_RESPONSE, array($this, 'injectResponse'), $priority);
    }

    public function selectRenderer(ViewEvent $e)
    {
        $model = $e->getModel();
        if (!$model instanceof CsvModel) {
            return;
        }
        return $this->renderer;
    }

    public function injectResponse(ViewEvent $e)
    {
        $renderer = $e->getRenderer();
        if ($renderer !== $this->renderer) {
            return;
        }

        $result = $e->getResult();

        if (empty($result)) {
            return;
        }

        $response = $e->getResponse();
        $response->setContent($result);
        $headers = $response->getHeaders();
        $headers->addHeaderLine('Content-Type', 'application/csv');
        $headers->addHeaderLine('Pragma', 'no-cache');
    }

}