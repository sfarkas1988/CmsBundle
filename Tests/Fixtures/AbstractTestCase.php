<?php

/*
* This file is part of the OrbitaleCmsBundle package.
*
* (c) Alexandre Rock Ancelet <alex@orbitale.io>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Orbitale\Bundle\CmsBundle\Tests\Fixtures;

use Doctrine\Bundle\DoctrineBundle\Command\CreateDatabaseDoctrineCommand;
use Doctrine\Bundle\DoctrineBundle\Command\Proxy\CreateSchemaDoctrineCommand;
use Orbitale\Bundle\CmsBundle\Entity\Page;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class AbstractTestCase.
 */
class AbstractTestCase extends WebTestCase
{
    /**
     * @var ContainerInterface
     */
    protected static $container;

    public function setUp()
    {
        $kernel = static::getKernel();

        $databaseFile = $kernel->getContainer()->getParameter('database_path');
        $application = new Application($kernel);

        if (file_exists($databaseFile)) {
            unlink($databaseFile);
        }

        // Create database
        $command = new CreateDatabaseDoctrineCommand();
        $application->add($command);
        $input = new ArrayInput(array('command' => 'doctrine:database:create'));
        $command->run($input, new NullOutput());

        // Create database schema
        $command = new CreateSchemaDoctrineCommand();
        $application->add($command);
        $input = new ArrayInput(array('command' => 'doctrine:schema:create'));
        $command->run($input, new NullOutput());
    }

    /**
     * @param array $options
     */
    protected static function bootKernel(array $options = array())
    {
        if (method_exists('Symfony\Bundle\FrameworkBundle\Test\KernelTestCase', 'bootKernel')) {
            parent::bootKernel($options);
        } else {
            if (null !== static::$kernel) {
                static::$kernel->shutdown();
            }
            static::$kernel = static::createKernel($options);
            static::$kernel->boot();
            static::$kernel;
        }
    }

    /**
     * @param array $options An array of options to pass to the createKernel class
     *
     * @return KernelInterface
     */
    protected function getKernel(array $options = array())
    {
        static::bootKernel($options);

        return static::$kernel;
    }

    /**
     * @param array $values
     *
     * @return Page
     */
    protected function createPage(array $values = array())
    {
        $page = new Page();
        foreach ($values as $key => $value) {
            $page->{'set'.ucFirst($key)}($value);
        }

        return $page;
    }
}
