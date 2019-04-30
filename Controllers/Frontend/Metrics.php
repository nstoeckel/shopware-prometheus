<?php

use OdsPrometheus\Metrics\PrometheusMetricInterface;
use Prometheus\RenderTextFormat;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2018 Hans Hoechtl <hhoechtl@1drop.de>
 *  All rights reserved
 ***************************************************************/

class Shopware_Controllers_Frontend_Metrics extends \Enlight_Controller_Action
{

    /**
     * @var \Prometheus\CollectorRegistry
     */
    protected $registry = null;

    /**
     * Execute all registered metrics to fill the registry
     */
    public function indexAction()
    {
        $this->Front()->Plugins()->ViewRenderer()->setNoRender();
        $this->registry = Shopware()->Container()->get('ods_prometheus.registry');
        /** @var array $registeredMetricServices */
        $registeredMetricServices = Shopware()->Container()->getParameter('ods_prometheus.metrics');
        foreach ($registeredMetricServices as $registeredMetricService) {
            /** @var PrometheusMetricInterface $metric */
            $metric = Shopware()->Container()->get($registeredMetricService);
            $metric->collectMetric();
        }
    }

    /**
     * Render the registry as Prometheus text and output it
     */
    public function postDispatch()
    {
        $renderer = new RenderTextFormat();
        $data = $renderer->render($this->registry->getMetricFamilySamples());
        $this->Response()->setHeader('Content-type', RenderTextFormat::MIME_TYPE, true);
        $this->Response()->setBody($data);
    }
}
