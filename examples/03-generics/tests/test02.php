<?php

namespace GenericsExample;

use GenericsExample\ORM\Entity\User;

$em = new \stdClass();
$repository = new ORM\Repository<User>($em);
