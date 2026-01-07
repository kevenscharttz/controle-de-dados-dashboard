<?php

namespace App\Providers\Filament;

/**
 * Small adapter class so generators/tools that expect a provider
 * named after the panel id (PainelPanelProvider) can find it.
 *
 * Your real implementation lives in AdminPanelProvider, which
 * already configures the panel id/path to 'painel'. This class
 * simply extends it so both names work.
 */
class PainelPanelProvider extends AdminPanelProvider
{
    // intentionally empty - inherits everything from AdminPanelProvider
}
