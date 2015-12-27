<?php

namespace THCFrame\Security\Authorization;

use THCFrame\Security\Model\BasicUserModel;

/**
 * AuthorizationInterface ensure that authorization class will have isGranted method
 */
interface AuthorizationInterface
{
    public function isGranted(BasicUserModel $user, $requiredRole);
}
