<?php

namespace Youngx\Bundle\UserBundle\Module\RoleModule\Listener;

use Youngx\EventHandler\Event\GetValueEvent;
use Youngx\MVC\Context;
use Youngx\MVC\Event\GetResponseEvent;
use Youngx\MVC\Request;
use Youngx\MVC\User\Identity;
use Youngx\Bundle\UserBundle\Entity\UserEntity;
use Youngx\EventHandler\Registration;

class RoleListener implements Registration
{
    /**
     * @var Context
     */
    protected $context;

    public function setContext(Context $context)
    {
        $this->context = $context;
    }

    public function roles(GetValueEvent $event, UserEntity $user)
    {
        $roles = array();

        if ($user->getUid()) {
            foreach ($this->context->db()->query("SELECT r.role_id, r.label FROM y_user_role ur LEFT JOIN y_role r ON ur.role_id=r.role_id WHERE ur.uid=:uid ORDER BY ur.role_id ASC", array(
                    ':uid' => $user->getUid()
                ))->fetchAll() as $row) {
                $roles[$row['role_id']] = $row['label'];
            }
        }

        $event->setValue($roles);
    }

    public function access(GetResponseEvent $event, Request $request, $access)
    {
        if ($this->context->identity()->hasRole(Identity::ROLE_ADMINISTRATOR)) {
            return true;
        }

        return $this->context->permit($access);
    }

    public function permit(array $roles, $permission)
    {
        if ($roles) {
            if (isset($roles[Identity::ROLE_ADMINISTRATOR])) {
                return true;
            }
            $roles = "'" . implode("', '", array_keys($roles)) . "'";
            return (Boolean) $this->context->db()->query("SELECT role_id FROM y_user_role_permission WHERE role_id IN ({$roles}) AND permission=:permission LIMIT 1", array(
                    ':permission' => $permission
                ))->fetchColumn(0);
        } else {
            return false;
        }
    }

    public static function registerListeners()
    {
        return array(
            'kernel.access' => 'access',
            'kernel.entity.user.field.roles' => 'roles',
            'kernel.permit' => 'permit'
        );
    }
}