<?php

namespace App\Command;

use App\Helpers\FicheroHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;

class ControlConsumosCommand extends Command
{
    protected static $defaultName = 'app:control-consumos';
    protected static $defaultDescription = 'Comando que devuelve las lecturas sospechosas';
    protected FicheroHelper $ficheroHelper;

    /**
     * Constructor.
     * @param string|null $name
     * @param FicheroHelper $ficheroHelper
     */
    public function __construct(string $name = null, FicheroHelper $ficheroHelper)
    {
        parent::__construct($name);
        $this->ficheroHelper = $ficheroHelper;
    }



    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('fichero', InputArgument::REQUIRED, 'Fichero a leer')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $nombreFichero = $input->getArgument('fichero');

        // Nos devuelve los datos ordenados por cliente
        $resultadosByCliente = $this->ficheroHelper->volcarDatos($nombreFichero);
        if (null === $resultadosByCliente) {
            return 0;
        }

        $arraySospechosos = [];
        // Recorremos el array de clientes y calculamos su mediana
        foreach ($resultadosByCliente as $cliente => $valores) {
            $medianaAnual = $this->ficheroHelper->getMediana($valores);
            $medianaBaja = $medianaAnual - ($medianaAnual * 0.5);
            $medianaAlta = $medianaAnual * 1.5;
            // Recorremos el array de datos del cliente para encontrar valores incorrectos
            foreach ($valores as $lecturas) {
                if ($lecturas['lectura'] <= $medianaBaja  || $lecturas['lectura'] >= $medianaAlta) {
                    $arraySospechosos[] = [
                        $cliente, $lecturas['periodo'], $lecturas['lectura'], $medianaAnual
                    ];
                }
            }
        }
        // Inicializamos la tabla que contendrá los valores
        $table = new Table($output);
        $table->setHeaders(['Cliente', 'Mes', 'Sospechoso', 'Mediana']);
        $table
            ->setRows($arraySospechosos);
        $table->render();
        $io->success('Comando finalizado');
        return 0;
    }
}
