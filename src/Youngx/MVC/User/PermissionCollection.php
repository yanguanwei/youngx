<?php

namespace Youngx\MVC\User;

class PermissionCollection
{
    private $groups = array();
    private $permissionGroups  = array();
    private $permissions = array();

    public function add($group, $name, $title)
    {
        if (!isset($this->groups[$group])) {
            $this->groups[$group] = $group;
        }
        $this->permissionGroups[$group][$name] = $name;
        $this->permissions[$name] = $title;

        return $this;
    }

    public function setGroup($group, $label)
    {
        $this->groups[$group] = $label;
    }

    public function addPermissions($group, array $permissions, $groupLabel = null)
    {
        if ($groupLabel !== null) {
            $this->setGroup($group, $groupLabel);
        }

        foreach ($permissions as $name => $title) {
            $this->add($group, $name, $title);
        }

        return $this;
    }

    public function getGroups()
    {
        return $this->groups;
    }

    public function getGroupLabel($group)
    {
        return isset($this->groups[$group]) ? $this->groups[$group] : null;
    }

    public function getPermissions($group)
    {
        $permissions = array();
        if (isset($this->permissionGroups[$group])) {
            foreach ($this->permissionGroups[$group] as $name) {
                $permissions[$name] = $this->permissions[$name];
            }
        }
        return $permissions;
    }
}