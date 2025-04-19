<?php
declare(strict_types=1);

namespace App\Orchid\Layouts\Application;

use Orchid\Screen\Layouts\View;

/**
 * Layout to render application details offcanvas.
 */
class ApplicationOffcanvasLayout extends View
{
    /**
     * ApplicationOffcanvasLayout constructor.
     */
    public function __construct()
    {
        // Render the blade view resources/views/application-offcanvas.blade.php
        parent::__construct('application-offcanvas');
    }
}