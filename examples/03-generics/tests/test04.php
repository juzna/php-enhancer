<?php

namespace GenericsExample;

use GenericsExample\ORM\Entity\User;
use GenericsExample\ORM\Repository;

$em = new \stdClass();
$repository = new Repository<User>($em);
