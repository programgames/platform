<?php

namespace Oro\Bundle\WorkflowBundle\Command;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Oro\Bundle\WorkflowBundle\Entity\ProcessTrigger;
use Oro\Bundle\WorkflowBundle\Model\ProcessData;
use Oro\Bundle\WorkflowBundle\Model\ProcessHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Handle process trigger with specified identifier and process name
 */
class HandleProcessTriggerCommand extends Command
{
    /** @var string */
    protected static $defaultName = 'oro:process:handle-trigger';

    /** @var ManagerRegistry */
    private $registry;

    /** @var ProcessHandler */
    private $processHandler;

    /**
     * @param ManagerRegistry $registry
     * @param ProcessHandler $processHandler
     */
    public function __construct(ManagerRegistry $registry, ProcessHandler $processHandler)
    {
        parent::__construct();

        $this->registry = $registry;
        $this->processHandler = $processHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->setDescription('Handle process trigger with specified identifier and process name')
            ->addOption(
                'name',
                null,
                InputOption::VALUE_REQUIRED,
                'Process definition name'
            )
            ->addOption(
                'id',
                null,
                InputOption::VALUE_REQUIRED,
                'Identifier of the process trigger'
            );
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $processName = $input->getOption('name');

        $triggerId = $input->getOption('id');
        if (!filter_var($triggerId, FILTER_VALIDATE_INT)) {
            $output->writeln('<error>No process trigger identifier defined</error>');
            return;
        }

        /** @var ProcessTrigger $processTrigger */
        $processTrigger = $this->registry->getRepository('OroWorkflowBundle:ProcessTrigger')->find($triggerId);
        if (!$processTrigger) {
            $output->writeln('<error>Process trigger not found</error>');
            return;
        }

        $processDefinition = $processTrigger->getDefinition();
        if ($processName !== $processDefinition->getName()) {
            $output->writeln(sprintf('<error>Trigger not found in process definition "%s"</error>', $processName));
            return;
        }

        $processData = new ProcessData();

        /** @var EntityManager $entityManager */
        $entityManager = $this->registry->getManager();
        $entityManager->beginTransaction();

        try {
            $start = microtime(true);

            $this->processHandler->handleTrigger($processTrigger, $processData);
            $entityManager->flush();
            $this->processHandler->finishTrigger($processTrigger, $processData);
            $entityManager->commit();

            $output->writeln(
                sprintf(
                    '<info>[%s] Trigger #%d of process "%s" successfully finished in %f s</info>',
                    (new \DateTime('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s'),
                    $triggerId,
                    $processDefinition->getName(),
                    microtime(true) - $start
                )
            );
        } catch (\Exception $e) {
            $this->processHandler->finishTrigger($processTrigger, $processData);
            $entityManager->rollback();

            $output->writeln(
                sprintf(
                    '<error>[%s] Trigger #%s of process "%s" failed: %s</error>',
                    (new \DateTime('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s'),
                    $triggerId,
                    $processDefinition->getName(),
                    $e->getMessage()
                )
            );

            throw $e;
        }
    }
}
