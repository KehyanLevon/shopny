<?php

namespace App\Enum;

enum PromoScopeType: string
{
    case ALL = 'all';
    case SECTION = 'section';
    case CATEGORY = 'category';
    case PRODUCT = 'product';
}
