<?php

use de\malkusch\autoloader\anynamespace;
namespace de\malkusch\autoloader\test\usetest1;

class Test
{

}

namespace de\malkusch\autoloader\test\usetest2;
use de\malkusch\autoloader\anynamespace;

class Test
{

}

use de\malkusch\autoloader\anynamespace as anynamespace1;
namespace de\malkusch\autoloader\test\usetest3;

class Test
{

}

namespace de\malkusch\autoloader\test\usetest4;
use de\malkusch\autoloader\anynamespace as anynamespace2;

class Test
{

}
