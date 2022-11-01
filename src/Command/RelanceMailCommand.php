<?php
/**
 * Created by PhpStorm.
 * User: T2G-WEB
 * Date: 18/10/2022
 * Time: 20:35
 */

namespace App\Command;


use App\Service\RelanceMailService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RelanceMailCommand extends Command
{

    private $relanceMailService;

    public function __construct(?string $name = null, RelanceMailService $relanceMailService)
    {
        parent::__construct($name);
        $this->relanceMailService = $relanceMailService;
    }

    protected function configure () {
        $this->setName('app:relancemail');

        $this->setDescription("Commande permettant de relancer les personnes servant le lendemain par mail");

    }

    public function execute (InputInterface $input, OutputInterface $output) {
        $this->relanceMailService->relanceMail();
    }
}