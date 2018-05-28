<?php

namespace AppBundle\Services;

use Doctrine\ORM\EntityManager;
use Liuggio\ExcelBundle\Factory;

/**
 * ExcelExporter
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class ExcelExporter
{
    const FORMAT_DATETIME = 'dd/mm/yyyy h:mm';

    /**
     * @var Factory
     */
    private $excelService;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var \DateTime
     */
    private $leftDate;

    /**
     * @var \DateTime
     */
    private $rightDate;

    /**
     * @var \PHPExcel
     */
    private $phpExcelObject;

    /**
     * ExcelExporter constructor.
     * @param Factory $excelService
     */
    public function __construct(Factory $excelService, EntityManager $entityManager)
    {
        $this->excelService = $excelService;
        $this->entityManager = $entityManager;
    }

    /**
     * @param \DateTime $leftDate
     * @param \DateTime $rightDate
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     * @throws \PHPExcel_Exception
     */
    public function export(\DateTime $leftDate, \DateTime $rightDate)
    {
        $this->leftDate = $leftDate;
        $this->rightDate = $rightDate;

        $this->phpExcelObject = $this->excelService->createPHPExcelObject();

        $x = $this->phpExcelObject->getSheetCount();
        for ($i = 0; $i < $x; $i++) {
            $this->phpExcelObject->removeSheetByIndex(0);
        }

        $this->addSheetOfReservas();

        $this->phpExcelObject->setActiveSheetIndex(0);

        $writer = $this->excelService->createWriter($this->phpExcelObject, 'Excel2007');

        return $this->excelService->createStreamedResponse($writer);
    }

    private function addSheetOfReservas()
    {
        $query = $this->entityManager
            ->getRepository('AppBundle:Reserva')
            ->createQueryBuilder('r')
            ->select(array(
                'r.id',
                'r.startAt',
                'r.endAt',
                'p.name AS provider',
                'd.name AS driver',
                'r.providerReference',
                'r.serviceDescription',
                'r.clientNames',
                'r.isExecuted',
                'r.isCancelled',
                'r.driverPayAmount',
                'r.paidAt',
                'r.clientPriceAmount',
                'r.cobradoAt',
                's.name AS serviceType',
                'g.name AS guide',
                'r.pax',
                'r.executionIssues',
                'r.createdBy',
                'r.updatedBy'
            ))
            ->join('r.provider', 'p')
            ->leftJoin('r.driver', 'd')
            ->join('r.serviceType', 's')
            ->leftJoin('r.guide', 'g')
            ->where('r.startAt >= :left_date AND r.startAt <= :right_date')
            ->orderBy('r.startAt')
            ->getQuery();

        $result = $query
            ->setParameters(array(
                'left_date' => $this->leftDate->format('Y-m-d'),
                'right_date' => $this->rightDate->format('Y-m-d 23:59:59')
            ))
            ->getResult();

        /** @var \PHPExcel_Worksheet $sheet */
        $sheet = $this->phpExcelObject->createSheet();

        foreach ($result as $index => $row) {
            //Confirmation number
            $sheet->setCellValueByColumnAndRow(
                0,
                $index + 2,
                sprintf('T%s-%04s', substr($row['startAt']->format('ymd'), 1),
                    substr($row['id'], -4))
            );
            //Start at
            $sheet->setCellValueByColumnAndRow(
                1,
                $index + 2,
                \PHPExcel_Shared_Date::PHPToExcel($row['startAt'])
                );
            //End at
            if ($row['endAt']) {
                $sheet->setCellValueByColumnAndRow(
                    2,
                    $index + 2,
                    \PHPExcel_Shared_Date::PHPToExcel($row['endAt'])
                );
            };
            //Provider
            $sheet->setCellValueByColumnAndRow(
                3,
                $index + 2,
                $row['provider']
            );
            //Driver
            $sheet->setCellValueByColumnAndRow(
                4,
                $index + 2,
                $row['driver']
            );
            //Provider reference
            $sheet->setCellValueByColumnAndRow(
                5,
                $index + 2,
                $row['providerReference']
            );
            //Service description
            if ($row['serviceDescription']) {
                if (preg_match('/<p[^>]*>/', $row['serviceDescription'])) {
                    $content = strip_tags($row['serviceDescription'], '<p>');
                    $content = str_replace("\n", '', $content);
                    $content = preg_replace(
                        array('/<p[^>]*>/', '@</p>@'),
                        array('', "\n"),
                        $content
                    );
                } else {
                    $content = $row['serviceDescription'];
                }
                $richTextObject = new \PHPExcel_RichText();
                $richTextObject->createText($content);
                $sheet->setCellValueByColumnAndRow(
                    6,
                    $index + 2,
                    $richTextObject
                );
            }
            //Clien names
            $sheet->setCellValueByColumnAndRow(
                7,
                $index + 2,
                $row['clientNames']
            );
            //State
            $sheet->setCellValueByColumnAndRow(
                8,
                $index + 2,
                $row['isExecuted'] ? 'EJECUTADA' : ($row['isCancelled'] ? 'CANCELADA' : '')
            );
            if ($row['isExecuted']) {
                $sheet->getStyleByColumnAndRow(
                    0,
                    $index + 2,
                    18,
                    $index + 2
                )->applyFromArray(
                    array(
                        'fill' => array(
                            'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('argb' => 'ff67D142')
                        )
                    )
                );
            } elseif ($row['isCancelled']) {
                $sheet->getStyleByColumnAndRow(
                    0,
                    $index + 2,
                    18,
                    $index + 2
                )->applyFromArray(
                    array(
                        'fill' => array(
                            'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('argb' => 'ffa9a9a9')
                        )
                    )
                );
            }

            //Driver pay amount
            $sheet->setCellValueByColumnAndRow(
                9,
                $index + 2,
                $row['driverPayAmount']
            );
            //Paid at
            if ($row['paidAt']) {
                $sheet->setCellValueByColumnAndRow(
                    10,
                    $index + 2,
                    \PHPExcel_Shared_Date::PHPToExcel($row['paidAt'])
                );
            }
            //Client price amount
            $sheet->setCellValueByColumnAndRow(
                11,
                $index + 2,
                $row['clientPriceAmount']
            );
            //Cobrado at
            if ($row['cobradoAt']) {
                $sheet->setCellValueByColumnAndRow(
                    12,
                    $index + 2,
                    \PHPExcel_Shared_Date::PHPToExcel($row['cobradoAt'])
                );
            }
            //Service type
            $sheet->setCellValueByColumnAndRow(
                13,
                $index + 2,
                $row['serviceType']
            );
            //Guide
            if ($row['guide']) {
                $sheet->setCellValueByColumnAndRow(
                    14,
                    $index + 2,
                    $row['guide']
                );
            }
            //Pax
            $sheet->setCellValueByColumnAndRow(
                15,
                $index + 2,
                $row['pax']
            );
            //Execution issues
            if ($row['executionIssues']) {
                $richTextObject = new \PHPExcel_RichText();
                $richTextObject->createText($row['executionIssues']);
                $sheet->setCellValueByColumnAndRow(
                    16,
                    $index + 2,
                    $richTextObject
                );
            }

            //Created by
            if ($row['createdBy']) {
                $sheet->setCellValueByColumnAndRow(
                    17,
                    $index + 2,
                    $row['createdBy']
                );
            }
            //Created by
            if ($row['updatedBy']) {
                $sheet->setCellValueByColumnAndRow(
                    18,
                    $index + 2,
                    $row['updatedBy']
                );
            }
        }

        $sheet
            ->getStyleByColumnAndRow(1, 1, 2, count($result))
            ->getNumberFormat()
            ->setFormatCode(self::FORMAT_DATETIME);
        $sheet
            ->getStyleByColumnAndRow(9, 1, 9, count($result))
            ->getNumberFormat()
            ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
        $sheet
            ->getStyleByColumnAndRow(10, 1, 10, count($result))
            ->getNumberFormat()
            ->setFormatCode(self::FORMAT_DATETIME);
        $sheet
            ->getStyleByColumnAndRow(11, 1, 11, count($result))
            ->getNumberFormat()
            ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
        $sheet
            ->getStyleByColumnAndRow(12, 1, 12, count($result))
            ->getNumberFormat()
            ->setFormatCode(self::FORMAT_DATETIME);
    }
}
