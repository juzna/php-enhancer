<?php

namespace GenericsExample;

use GenericsExample\ORM\Entity\User;

$repository = new ORM\Repository<User>($em);
