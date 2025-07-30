<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class ManajemenMenu extends Cluster
{
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?string $navigationLabel = 'Manajemen Menu';
    protected static ?string $navigationGroup = 'Restoran';
    protected static ?string $clusterBreadcrumb = 'Manajemen Menu';
}
