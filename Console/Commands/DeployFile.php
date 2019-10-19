<?php

namespace ByterCold\Deploy\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Exception\LogicException;
use ByterCold\Deploy\Model\StaticProcessor;
use Magento\Framework\App\State;
use ByterCold\Deploy\Helper\Data as Helper;

/**
 * DeployFile class
 */
class DeployFile extends Command
{
    /**
     * @var State
     */
    protected $state;

    /**
     * @var array
     */
    protected $reloadVersionOptions = [
        Helper::OPTION_RELOAD_VERSION_NO,
        Helper::OPTION_RELOAD_VERSION_YES
    ];
    
    /**
     * Class constructor
     *
     * @param string $name
     * @param StaticProcessor $staticProcessor
     */
    public function __construct(
        StaticProcessor $staticProcessor,
        State $state,
        string $name = null
    ) {
        parent::__construct($name);
        $this->staticProcessor = $staticProcessor;
        $this->state = $state;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $options = [
            new InputOption(
                'staticFile',
                'f',
                InputOption::VALUE_REQUIRED,
                sprintf("Static File")
            ),
            new InputOption(
                'reloadVersion',
                'r',
                InputOption::VALUE_REQUIRED,
                sprintf("Reload Version")
            )
        ];
        
        $this->setName('bytercold:instant-static-deploy:file')
            ->setDescription('A better way to deploy a single static file when the Deploy Mode is set to Production :)')
            ->setDefinition($options);

        parent::configure();
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $staticFile = $input->getOption('staticFile');
        if (!$staticFile) {
            $output->writeln("<error>Parameter 'staticFile' is missing.</error>");
            return;
        }
        $reloadVersion = false;
        $reloadVersionInput = $input->getOption('reloadVersion');
        if (!is_numeric($reloadVersionInput) || !in_array($reloadVersionInput, $this->reloadVersionOptions)) {
            $output->writeln(sprintf("<error>Incorrect value for 'reloadVersion' parameter. [%s]</error>", implode("-", $this->reloadVersionOptions)));
            return;
        }
        if ($reloadVersionInput) {
            $reloadVersion = true;
        }

        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);

        $this->staticProcessor->processStaticFile($staticFile, $reloadVersion, $output);
    }
}
