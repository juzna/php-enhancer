<?php

namespace GenericsExample;

use GenericsExample\ORM\Entity\User;
use GenericsExample\ORM\Repository;

$repository = new Repository<User>($em);
