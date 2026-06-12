<?php

declare(strict_types=1);

namespace App\View\Composers;

use Roots\Acorn\View\Composer;

use function App\solidshop_get_mega_menu;

/**
 * Supplies the cached mega-menu array to the catalog dropdown partial.
 * Передає кешований масив мега-меню в партіал дропдауна каталогу.
 */
class MegaMenu extends Composer
{
    /**
     * List of views served by this composer.
     * Список view, які обслуговує цей composer.
     *
     * @var array
     */
    protected static $views = [
        'partials.mega-menu',
    ];

    /**
     * Data passed to the view.
     * Дані, що передаються у view.
     */
    public function with(): array
    {
        return [
            'megaMenu' => function_exists('App\\solidshop_get_mega_menu')
                ? solidshop_get_mega_menu()
                : [],
        ];
    }
}
