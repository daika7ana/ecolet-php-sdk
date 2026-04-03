<?php

declare(strict_types=1);

namespace Daika7ana\Ecolet\Enums;

enum ParcelType: string
{
    case Package = 'package';
    case Envelope = 'envelope';
    case Pallet = 'pallet';
}
