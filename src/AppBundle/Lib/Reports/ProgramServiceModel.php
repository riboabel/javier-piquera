<?php

namespace AppBundle\Lib\Reports;

use AppBundle\Lib\Reports\Report;
use Doctrine\ORM\EntityManager;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberFormat;
use AppBundle\Entity\Reserva;
use AppBundle\Entity\ReservaPassingPlace;

class ProgramServiceModel extends Report
{
    /**
     * @var Reserva
     */
    private $reserva;

    /**
     * @var PhoneNumberUtil
     */
    private $phoneService;

    /**
     * @var EntityManager
     */
    private $manager;

    /**
     * @var string
     */
    private $logoPath;

    public function __construct(Reserva $reserva, PhoneNumberUtil $phoneService, EntityManager $manager, $logoPath)
    {
        parent::__construct('P', 'LETTER');

        $this->reserva = $reserva;
        $this->phoneService = $phoneService;
        $this->manager = $manager;
        $this->logoPath = $logoPath;
    }

    public function getContent()
    {
        $this->pdf->addPage();

        $this->renderHeader();
        $this->render();

        return $this->getPdfContent();
    }

    private function renderHeader()
    {
        $this->pdf->SetFont('Helvetica', 'B', 15);

        $this->pdf->Cell(0, 0, $this->reserva->getServiceType()->getName(), 0, 1, 'C');
        $this->pdf->Ln(4);
        
        if (null !== $this->reserva->getProvider()->getLogoName()) {
            $this->pdf->SetFont('', '', 9);
            $h = $this->getRowHeight(array(
                array(80, ''),
                array(20, ''),
                array(0, $this->reserva->getProvider()->getPostalAddress())
            ));
            $this->pdf->MultiCell(110, $h, '', 0, 'J', false, 0);
            $imagePath = sprintf('%s/%s', $this->logoPath, $this->reserva->getProvider()->getLogoName());
            $x = $this->pdf->GetX();
            $y = $this->pdf->GetY();
            $this->pdf->MultiCell(20, $h, '', 0, 'L', false, 0);
            $this->pdf->Image($imagePath, $x + 0.5, $y + 0.5, 19, $h - 1, '', '', '', 2, 300, '', false, false, 0, 'CM');
            $this->pdf->MultiCell(0, $h, $this->reserva->getProvider()->getPostalAddress(), 0, 'L');
        } else {
            $this->pdf->SetFont('', 'B', 12);
            $this->pdf->Cell(110, 0, '', 1);
            $this->pdf->Cell(20, 0, $this->reserva->getProvider()->getName(), 1, 0, 'L');
            $this->pdf->SetFont('', '', 12);
            $this->pdf->Cell(0, 0, $this->reserva->getProvider()->getPostalAddress(), 1, 1, 'L');
        }

        $this->pdf->Ln(5);
    }

    private function render()
    {
        $this->pdf->SetFont('', 'B', 10);

        $this->pdf->Cell(80, 0, 'LIST OF HOTELS', 'LTB');
        $this->pdf->Cell(0, 0, 'DATE', 'TRB', 1);

        $this->pdf->SetFont('', '', '9');
        $this->pdf->Cell(0, 0, 'Tour Operator:', 0, 1);

        $this->pdf->Cell(50, 0, sprintf('%s booking code:', $this->reserva->getProvider()->getName()), 'LT');
        $this->pdf->Cell(0, 0, $this->reserva->getProviderReference(), 'TR', 1);
        $this->pdf->Cell(50, 0, 'TTOO reference number:', 'L');
        $this->pdf->Cell(0, 0, $this->reserva->getSerialNumber(), 'R', 1);
        $this->pdf->Cell(50, 0, 'Client names:', 'L');
        $this->pdf->Cell(0, 0, $this->reserva->getClientNames(), 'R', 1);
        $this->pdf->Cell(50, 0, 'TTOO program names:', 'L');
        $this->pdf->Cell(0, 0, 'Taxi', 'R', 1);
        $this->pdf->Cell(50, 0, 'Number of passengers:', 'LB');
        $this->pdf->Cell(0, 0, $this->reserva->getPax(), 'RB', 1);

        $this->pdf->Ln(3);

        $this->renderPickupLine();
        $this->pdf->Ln(3);

        $this->renderPassingPlaces();
        $this->pdf->Ln(3);

        $this->renderDropOffLine();
        $this->pdf->Ln(4);

        $this->renderProviderEmergencyInfo();
        $this->pdf->Ln(3);

        $this->pdf->MultiCell(0, 0, sprintf('Guide: %s', $this->reserva->getGuide() ? $this->reserva->getGuide() : $this->reserva->getDriver()), 1, 'L');
        $this->pdf->Ln(3);
        
        $this->pdf->MultiCell(0, 0, $this->reserva->getServiceDescription(), 1, 'L');
    }

    private function renderPickupLine()
    {
        $h = $this->getRowHeight(array(
            array(20, 'Pick up'),
            array(0, sprintf("%s at %s\n%s", $this->reserva->getStartAt()->format('d/m/Y H:i'), $this->reserva->getStartPlace()->getName(), $this->reserva->getStartPlace()->getPostalAddress()))
        ));

        $this->pdf->SetFont('', 'B');
        $this->pdf->MultiCell(20, $h, 'Pick up', 'LTB', 'L', false, 0);
        $this->pdf->SetFont('', '');
        $this->pdf->MultiCell(0, $h, sprintf("%s at %s\n%s", $this->reserva->getStartAt()->format('d/m/Y H:i'), $this->reserva->getStartPlace()->getName(), $this->reserva->getStartPlace()->getPostalAddress()), 'RTB', 'L');
    }

    private function renderPassingPlaces()
    {
        $this->pdf->SetFont('', 'B');
        $this->pdf->Cell(30, 0, 'Date/Nights', 1, 0, 'C');
        $this->pdf->Cell(35, 0, 'Hotel/Private house', 1, 0, 'C');
        $this->pdf->Cell(40, 0, 'City', 1, 0, 'C');
        $this->pdf->Cell(60, 0, 'Address', 1, 0, 'C');
        $this->pdf->Cell(0, 0, 'Phones', 1, 1, 'C');

        $places = $this->convertPassingPlaces();

        $this->pdf->SetFont('', '');
        foreach ($places as $place) {
            $h = $this->getRowHeight(array(
                array(30, sprintf('%s | %s nights', $place['first_date']->format('d/m/y'), $place['nights'])),
                array(35, $place['name']),
                array(40, $place['location']),
                array(60, $place['postal_address']),
                array(0, '')
            ));

            $this->pdf->MultiCell(30, $h, sprintf('%s | %s nights', $place['first_date']->format('d/m/y'), $place['nights']), 1, 'L', false, 0);
            $this->pdf->MultiCell(35, $h, $place['name'], 1, 'L', false, 0);
            $this->pdf->MultiCell(40, $h, $place['location'], 1, 'C', false, 0);
            $this->pdf->MultiCell(60, $h, $place['postal_address'], 1, 'L', false, 0);
            $this->pdf->MultiCell(0, $h, '', 1, 'C');
        }
    }

    /**
     * @return array
     */
    private function convertPassingPlaces()
    {
        $places = array_map(function(ReservaPassingPlace $place) {
            $phones = array();
            if ($place->getPlace()->getMobilePhone()) {
                $phones[] = $this->getPhoneNumber($place->getPlace()->getMobilePhone());
            }
            if ($place->getPlace()->getFixedPhone()) {
                $phones[] = $this->getPhoneNumber($place->getPlace()->getFixedPhone());
            }

            return array(
                'first_date' => $place->getStayAt(),
                'name' => $place->getPlace()->getName(),
                'location' => $place->getPlace()->getLocation() ? $place->getPlace()->getLocation()->getName() : '',
                'postal_address' => $place->getPlace()->getPostalAddress(),
                'phones' => implode(', ', $phones)
            );
        }, $this->reserva->getPassingPlaces()->toArray());

        usort($places, function($first, $last) {
            if ($first['first_date'] < $last['first_date']) {
                return -1;
            } elseif ($first['first_date'] > $last['first_date']) {
                return 1;
            }

            return 0;
        });

        $this->fillDatesInfo($places);

        return $places;
    }

    /**
     * @param array $places
     */
    private function fillDatesInfo(&$places)
    {
        for ($i = 0; $i < count($places) - 1; $i++) {
            $places[$i]['last_date'] = $places[$i + 1]['first_date'];
            $nights = date_diff($places[$i]['last_date'], $places[$i]['first_date'], true)->days;
            $places[$i]['nights'] = $nights;
        }

        $places[count($places) - 1]['last_date'] = $this->reserva->getEndAt();
        $places[count($places) - 1]['nights'] = date_diff($places[count($places) - 1]['last_date'], $places[count($places) - 1]['first_date'])->days;
    }

    private function renderDropOffLine()
    {
        $h = $this->getRowHeight(array(
            array(20, 'Drop off'),
            array(0, sprintf("%s at %s\n%s", $this->reserva->getEndAt()->format('d/m/Y H:i'), $this->reserva->getEndPlace()->getName(), $this->reserva->getEndPlace()->getPostalAddress()))
        ));

        $this->pdf->SetFont('', 'B');
        $this->pdf->MultiCell(20, $h, 'Drop off', 'LTB', 'L', false, 0);
        $this->pdf->SetFont('', '');
        $this->pdf->MultiCell(0, $h, sprintf("%s at %s\n%s", $this->reserva->getEndAt()->format('d/m/Y H:i'), $this->reserva->getEndPlace()->getName(), $this->reserva->getEndPlace()->getPostalAddress()), 'RTB', 'L');
    }

    private function renderProviderEmergencyInfo()
    {
        $this->pdf->MultiCell(0, 0, $this->reserva->getProvider()->getContactInfo(), 1, 'C');
    }

    private function getPhoneNumber($phone)
    {
        return null !== $phone ? $this->phoneService->format($phone, PhoneNumberFormat::INTERNATIONAL) : '';
    }
}
