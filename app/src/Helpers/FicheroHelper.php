<?php
/**
 * @author   "Igor Morillo>
 *
 * @version  1.0
 *
 * @license  Revised BSD
 */


declare(strict_types=1);

namespace App\Helpers;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;

class FicheroHelper
{
    /**
     * Constructor del Helper para lectura de ficheros XML
     *
     * @param LoggerInterface $logger
     * @param ParameterBagInterface $param
     */
    public function __construct(LoggerInterface $logger, ParameterBagInterface $param)
    {
        $this->logger = $logger;
        $this->param = $param;
    }

    /**
     * @param string $nombre
     * @return array|null
     */
    public function volcarDatos (string $nombre):? array
    {
        $arrayDatos = [];
        /** @var File|null $fichero */
        $fichero = $this->getFichero($nombre);
        if (null === $fichero) {
            return null;
        }
        // Parsemos el fichero en función de su tipo
        switch ($fichero->getExtension()) {
            case 'xml':
                // Usamos la clase DOMCrawler para leer el array
                $crawler = new Crawler();
                $crawler->addXmlContent(file_get_contents($fichero->getRealPath()));
                // Obtenemos los valores para cada nodo
                $valores = $crawler
                    ->filterXpath('//reading')
                    ->extract(['clientID', 'period', '_text']);
                break;
            case 'csv':
                $rowNo = 1;
                // $fp is file pointer to file sample.csv
                if ((false !== $fp = fopen($fichero->getRealPath(), 'rb'))) {
                    while (($row = fgetcsv($fp, 1000, ",")) !== FALSE) {
                        if (1 === $rowNo++) {
                            continue;
                        }
                        $valores[] = $row;
                    }
                    fclose($fp);
                }
                break;
            default:
                $this->logger->error('El fichero indicado no tiene una extensión válida (solo se aceptan ficheros CSV y XML)');
                return null;
        }
        return $this->setValores($valores);
//        return $valores;
    }

    /**
     * Devuelve el fichero asociado
     *
     * @param string $nombre
     * @return File|null
     */
    private function getFichero (string $nombre) :? File
    {
        $pathFichero = $this->param->get('kernel.project_dir').'/'.$nombre;
        try {
            return new File($pathFichero, true);
        } catch (FileNotFoundException $fileNotFoundException) {
            $this->logger->error( 'El fichero solicitado no existe');
            return null;
        }
    }

    /**
     * Guarda los valores ordenados por cliente
     *
     * @param array $valores
     * @return array
     */
    private function setValores (array $valores): array
    {
        // Populamos el array
        foreach ($valores as $valor) {
            $tmp = [];
            $tmp['periodo'] = $valor[1];
            $tmp['lectura'] = $valor[2];
            $arrayDatos[$valor[0]][] = $tmp;
        }
        return $arrayDatos;
    }

    /**
     * Devuelve la mediana de los valores del array
     *
     * @param array $arrayResultados
     * @return float
     */
    public function getMediana(array $arrayResultados) : float
    {
        // obtenemos las lecturas
        $lecturas = array_column($arrayResultados, 'lectura');
        // ordenamos de menor a mayor el array
        sort($lecturas);
        // Obtenemos el índice medio (si es un float lo convertimos a interger)
        $middleIndex = count($lecturas) / 2;
        // Si el array es impar devolvemos el valor del medio
        if (is_float($middleIndex)) {
            return $lecturas[(int) $middleIndex];
        }
        // Si tenemos una cantidad de vlaores par, devolvemos la media de los valores intermedios
        return ($lecturas[$middleIndex] + $lecturas[$middleIndex - 1]) / 2;
    }
}