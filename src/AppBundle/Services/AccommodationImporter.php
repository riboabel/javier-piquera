<?php
/**
 * Created by PhpStorm.
 * User: Raibel
 * Date: 9/15/2019
 * Time: 9:15 AM
 */

namespace AppBundle\Services;

use AppBundle\Entity\HAccommodation;
use AppBundle\Entity\HProvider;
use AppBundle\Entity\HRegion;
use AppBundle\Validator\Constraints\AccommodationFileValidator;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class AccommodationImporter
{
    private $factory;
    private $manager;
    private $rows;
    private $cache = [];

    public function __construct(\Liuggio\ExcelBundle\Factory $factory, EntityManager $manager)
    {
        $this->factory = $factory;
        $this->manager = $manager;

        $this->cache['region'] = [];
        $this->cache['provider'] = [];
    }

    public function import(UploadedFile $file = null, $removeBeforeImport, $year, $month)
    {
        if ($removeBeforeImport) {
            $this->removeBeforeImport($year, $month);
        }

        $this->readFileContent($file);

        $this->manager->getConnection()->beginTransaction();
        try {
            $this->importRows();
            $this->manager->getConnection()->commit();
        } catch (\Exception $e) {
            $this->manager->rollback();

            throw new \RuntimeException('No es poisble importar el archivo.' . $e->getMessage());
        }

        return count($this->rows);
    }

    private function removeBeforeImport($year, $month)
    {
        $this->manager->getRepository('AppBundle:HAccommodation')->removeInMonth($year, $month);
    }

    private function readFileContent(UploadedFile $file)
    {
        $book = $this->factory->createPHPExcelObject($file->getPathname());

        $book->setActiveSheetIndex(0);
        $sheet = $book->getActiveSheet();

        $columns = $this->locateColumnsPosition($sheet);
        $lastColumnIndex = \PHPExcel_Cell::columnIndexFromString($sheet->getHighestDataColumn(1));

        $rows = [];
        foreach ($sheet->getRowIterator(2) as $sheetRow) {
            $row = [];
            for ($i = 0; $i < $lastColumnIndex; $i++) {
                $cell = $sheet->getCellByColumnAndRow($i, $sheetRow->getRowIndex());
                if (false !== ($key = array_search($i, $columns))) {
                    if (null === $cell->getValue()) {
                        $row[$key] = null;
                    } elseif (\PHPExcel_Shared_Date::isDateTime($cell)) {
                        $row[$key] = date_create_from_format('U', \PHPExcel_Shared_Date::ExcelToPHP($cell->getValue(), false));
                    } else {
                        $row[$key] = $cell->getValue();
                    }
                }
            }
            $rows[] = $row;
        }

        $this->rows = array_values($rows);
    }

    private function locateColumnsPosition($sheet)
    {
        $columns = [];
        $lastColumn = \PHPExcel_Cell::columnIndexFromString($sheet->getHighestDataColumn(1));
        for ($i = 0; $i < $lastColumn; $i++) {
            $cell = $sheet->getCellByColumnAndRow($i, 1);
            if ($cell->getValue() && in_array($cell->getValue(), AccommodationFileValidator::COLUMNS)) {
                $columns[$cell->getValue()] = $i;
            }
        }

        return $columns;
    }

    private function importRows()
    {
        foreach ($this->rows as $row) {
            $region = $this->getRegion(preg_replace('/(.+)\s\([^)]+\)$/', '$1', $row['PrimaryLocation']));
            $provider = $this->getProvider(preg_replace('/(.+)\s\([^)]+\)$/', '$1', $row['ServiceName']), $region['entity']);

            $accommodation = new HAccommodation();
            $accommodation
                ->setStartDate($row['StartDate'])
                ->setEndDate($row['EndDate'])
                ->setNights((int) $row['Nights'])
                ->setReference($row['OurReference'])
                ->setLeadClient($row['LeadClient'])
                ->setPax($row['Pax'])
                ->setCost($row['Cost'])
                ->setDetails(null)
                ->setProvider($provider['entity']);

            $this->manager->persist($accommodation);
        }

        $this->manager->flush();
    }

    private function getRegion($name)
    {
        $cachedRegion = array_filter($this->cache['region'], function($region) use ($name) {
            return $region['name'] == $name;
        });

        if ($cachedRegion) {
            return array_values($cachedRegion)[0];
        }

        $savedRegion = $this->manager->getRepository('AppBundle:HRegion')->findOneBy(['name' => $name]);
        if (!$savedRegion) {
            $savedRegion = new HRegion();
            $savedRegion->setName($name);
            $this->manager->persist($savedRegion);
            $this->manager->flush();
        }

        $this->cache['region'][] = [
            'name' => $name,
            'id' => $savedRegion->getId(),
            'entity' => $savedRegion
        ];

        return $this->cache['region'][count($this->cache['region']) - 1];
    }

    private function getProvider($name, HRegion $region)
    {
        $cachedProvider = array_filter($this->cache['provider'], function($provider) use($name) {
            return $provider['name'] == $name;
        });

        if ($cachedProvider) {
            return array_values($cachedProvider)[0];
        }

        $savedProvider = $this->manager->getRepository('AppBundle:HProvider')->findOneBy(['name' => $name]);
        if (!$savedProvider) {
            $savedProvider = new HProvider();
            $savedProvider
                ->setName($name)
                ->setRegion($region)
                ;
            $this->manager->persist($savedProvider);
            $this->manager->flush();
        }

        $this->cache['provider'][] = [
            'id' => $savedProvider->getId(),
            'name' => $savedProvider->getName(),
            'entity' => $savedProvider
        ];

        return $this->cache['provider'][count($this->cache['provider']) - 1];
    }
}