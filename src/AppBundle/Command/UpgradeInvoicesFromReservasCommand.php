<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Entity\Invoice;
use AppBundle\Entity\InvoiceLine;

/**
 * UpgradeInvoicesFromReservasCommand
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
class UpgradeInvoicesFromReservasCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this->setName('app:invoices:upgrade');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $manager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $counter = 0;

        foreach ($this->getReservasQuery()->getResult() as $reserva) {
            $invoice = new Invoice();

            $invoice
                    ->setModelName('ATRIO')
                    ->setDriver($reserva->getInvoiceDriver() ?: $reserva->getDriver())
                    ->setProvider($reserva->getProvider())
                    ->setSerialNumber($reserva->getInvoiceNumber())
                    ->setTotalCharge($reserva->getInvoicedTotalPrice())
                    ;

            $serviceLine = new InvoiceLine();
            $serviceLine
                    ->setServiceName($reserva->getServiceType()->getName())
                    ->setServiceSerialNumber($reserva->getSerialNumber())
                    ->setClientReference($reserva->getProviderReference())
                    ->setMeassurementUnit('Km')
                    ->setQuantity($reserva->getInvoicedKilometers())
                    ->setUnitPrice($reserva->getInvoicedKilometerPrice())
                    ->setTotalPrice($reserva->getInvoicedKilometersPrice())
                    ;
            $invoice->addLine($serviceLine);

            $hoursLine = new InvoiceLine();
            $hoursLine
                    ->setServiceName('Horas de espera')
                    ->setServiceSerialNumber($reserva->getSerialNumber())
                    ->setClientReference($reserva->getProviderReference())
                    ->setMeassurementUnit('hora')
                    ->setQuantity($reserva->getInvoicedHours())
                    ->setUnitPrice($reserva->getInvoicedHourPrice())
                    ->setTotalPrice($reserva->getInvoicedHoursPrice())
                    ;

            $invoice->addLine($hoursLine);

            $manager->persist($invoice);

            $counter++;
        }

        $manager->flush();

        $output->writeln(sprintf('Done! %s invoices created.', $counter));
    }

    private function getReservasQuery()
    {
        $manager = $this->getContainer()->get('doctrine.orm.entity_manager');

        $query = $manager->createQuery('SELECT r FROM AppBundle:Reserva AS r WHERE r.invoicedAt IS NOT NULL ORDER BY r.invoicedAt');

        return $query;
    }
}
