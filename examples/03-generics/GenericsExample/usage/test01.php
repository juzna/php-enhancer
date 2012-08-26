<?php

namespace GenericsExample;

$em = new \stdClass();
$repository = new ORM\Repository<ORM\Entity\User>($em);
