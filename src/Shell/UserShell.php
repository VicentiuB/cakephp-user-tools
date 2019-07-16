<?php
declare(strict_types = 1);

/**
 * UserShell
 *
 * @author Florian Krämer
 * @copyright Florian Krämer
 * @license MIT
 */
namespace Burzum\UserTools\Shell;

use Cake\Cache\Cache;
use Cake\Console\Shell;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;
use Exception;

/**
 * Users Shell
 */
class UserShell extends Shell
{

    /**
     * @var \Cake\ORM\Table
     */
    protected $UserTable;

    /**
     * Assign $this->connection to the active task if a connection param is set.
     *
     * @return void
     */
    public function startup()
    {
        parent::startup();
        Cache::disable();

        $this->UserTable = TableRegistry::getTableLocator()->get($this->param('model'), [
            'connection' => ConnectionManager::get($this->param('connection'))
        ]);

        if (!$this->UserTable->hasBehavior('Burzum/UserTools.User')) {
            $this->UserTable->addBehavior('Burzum/UserTools.User');
        }

        try {
            $this->UserTable->getSchema();
        } catch (Exception $e) {
            $this->err($e->getMessage());
            $this->_stop(1);
        }
    }

    /**
     * Removes inactive users
     *
     * @return void
     * @throws \Aura\Intl\Exception
     */
    public function removeInactive()
    {
        $count = $this->UserTable->removeInactiveUsers();
        $this->out(__dn(
            'burzum/user_tools',
            'Removed {0,number,integer} inactive user.',
            'Removed {0,number,integer} inactive users.',
            $count,
            $count
        ));
    }

    /**
     * Removes expired registrations
     *
     * @return void
     * @throws \Aura\Intl\Exception
     */
    public function removeExpired()
    {
        $count = $this->UserTable->removeExpiredRegistrations();
        $this->out(__dn(
            'burzum/user_tools',
            'Removed {0,number,integer} expired registration.',
            'Removed {0,number,integer} expired registrations.',
            $count,
            $count
        ));
    }

    /**
     * Sets a new password for an user.
     * cake user setPassword <searchTerm> <newPassword> <field | optional>
     *
     * @return void
     * @throws \Aura\Intl\Exception
     */
    public function setPassword()
    {
        if (count($this->args) < 2) {
            $this->abort(__d('burzum/user_tools', 'You need to call this command with at least tow arguments.'));
        }

        $field = 'username';
        if (count($this->args) >= 3) {
            $field = $this->args[2];
        }

        $user = $this->UserTable->find()->where([$field => $this->args[0]])->first();
        $user->password = $this->UserTable->hashPassword($this->args[1]);
        if ($this->UserTable->save($user, ['validate' => false])) {
            $this->out('Password saved');
        }
    }

    /**
     * Gets the option parser instance and configures it.
     *
     * @return \Cake\Console\ConsoleOptionParser
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();

        $parser->setDescription(
            'Users utility shell'
        )
        ->addOption('model', [
            'short' => 'm',
            'help' => 'User model to load',
            'default' => 'Users'
        ])
        ->addOption('connection', [
            'short' => 'c',
            'help' => 'The connection to use',
            'default' => 'default'
        ])
        ->addOption('behavior', [
            'short' => 'b',
            'help' => 'Auto-load the behavior if the model doesn\'t have it loaded.',
            'default' => 1
        ]);

        return $parser;
    }
}
