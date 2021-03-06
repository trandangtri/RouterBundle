<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\RouterBundle\Routing;

use ONGR\ElasticsearchBundle\Mapping\MetadataCollector;
use Symfony\Cmf\Component\Routing\ProviderBasedGenerator;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Route;

class DocumentUrlGenerator extends ProviderBasedGenerator
{
    /**
     * @var array Route map configuration to map Elasticsearch types and Controllers.
     */
    private $routeMap;

    /**
     * @var MetadataCollector
     */
    private $collector;

    /**
     * @return mixed
     */
    public function getRouteMap()
    {
        return $this->routeMap;
    }

    /**
     * @param mixed $routeMap
     */
    public function setRouteMap($routeMap)
    {
        $this->routeMap = $routeMap;
    }

    /**
     * @return MetadataCollector
     */
    public function getCollector()
    {
        return $this->collector;
    }

    /**
     * @param MetadataCollector $collector
     */
    public function setCollector(MetadataCollector $collector)
    {
        $this->collector = $collector;
    }

    /**
     * {@inheritdoc}
     */
    public function generate($name, $parameters = array(), $referenceType = self::ABSOLUTE_PATH)
    {
        try {
            $document = $parameters['document'];

            if (is_object($document)) {
                $documentUrl = $document->url;
            } else {
                $documentUrl = $document['url'];
            }

            $type = $this->collector->getDocumentType(get_class($document));
            $route = new Route(
                $documentUrl,
                [
                    '_controller' => $this->routeMap[$type],
                    'document' => $document,
                    'type' => $type,
                ]
            );

            // the Route has a cache of its own and is not recompiled as long as it does not get modified
            $compiledRoute = $route->compile();
            $hostTokens = $compiledRoute->getHostTokens();

            $debug_message = $this->getRouteDebugMessage($name);

            return $this->doGenerate(
                $compiledRoute->getVariables(),
                $route->getDefaults(),
                $route->getRequirements(),
                $compiledRoute->getTokens(),
                $parameters,
                $debug_message,
                $referenceType,
                $hostTokens
            );

        } catch (\Exception $e) {
            throw new RouteNotFoundException('Document is not correct or route cannot be generated.');
        }
    }
}
