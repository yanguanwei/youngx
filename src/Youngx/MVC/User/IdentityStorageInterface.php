<?php

namespace Youngx\MVC\User;

interface IdentityStorageInterface
{
	public function clear();
	public function read();
	public function write(Identity $identity, $duration);
}
