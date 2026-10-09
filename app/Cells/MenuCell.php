<?php

namespace App\Cells;

use CodeIgniter\View\Cells\Cell;

/**
 * Menu piloté par app/Models/json/Menus.json : `<?= view_cell('App\Cells\MenuCell', ['position' => 'mainmenu']) ?>`.
 *
 * Le Cell filtre les entrées d'après les droits ACL de l'utilisateur ; le balisage est dans menu.php.
 * Aucun contenu n'est produit si l'utilisateur n'a accès à aucune entrée.
 */
class MenuCell extends Cell
{
    /** Clé de Menus.json : sysmenu, optionmenu, mainmenu… */
    public string $position = '';

    // Propriétés calculées exposées à la vue par les méthodes get…Property() ci-dessous
    protected string $type = '';
    protected string $icon = '';
    /** @var list<array<string, mixed>> */
    protected array $entries = [];
    protected bool $visible = false;

    /** @var array<string, object>|null */
    private static ?array $definitions = null;

    /** @var array{type: string, icon: string, entries: list<array<string, mixed>>, visible: bool}|null */
    private ?array $resolved = null;

    public function getTypeProperty(): string
    {
        return $this->resolve()['type'];
    }

    public function getIconProperty(): string
    {
        return $this->resolve()['icon'];
    }

    /** @return list<array<string, mixed>> */
    public function getEntriesProperty(): array
    {
        return $this->resolve()['entries'];
    }

    public function getVisibleProperty(): bool
    {
        return $this->resolve()['visible'];
    }

    private function definition(): ?object
    {
        self::$definitions ??= (array) json_decode(file_get_contents(APPPATH . 'Models/json/Menus.json'));

        return self::$definitions[$this->position] ?? null;
    }

    private function resolve(): array
    {
        if ($this->resolved !== null) {
            return $this->resolved;
        }

        $acl     = service('acl');
        $def     = $this->definition();
        $visible = false;
        $entries = [];

        if ($def === null) {
            log_message('warning', 'MenuCell : position "' . $this->position . '" inconnue.');
        } elseif ($def->type === 'li') {
            // Menu principal : chaque entrée a son propre sous-menu (liens filtrés par ACL)
            foreach ($def->items as $element) {
                $entry = ['color' => $element->color, 'href' => null, 'label' => '', 'sub' => []];
                if ($acl->hasAccess(strtolower($element->url)) || $element->noright) {
                    $visible        = true;
                    $entry['href']  = $element->noright ? $element->url : base_url($element->url);
                    $entry['label'] = tr($element->name . ($element->opt ? '_' . $acl->getType() : ''));
                }
                foreach ($element->items as $sub) {
                    if ($sub->type === 'divider') {
                        $entry['sub'][] = ['divider' => true];
                    } elseif ($sub->type === 'link' && $acl->hasAccess(strtolower($sub->url))) {
                        $entry['sub'][] = ['href' => base_url($sub->url), 'label' => tr($sub->name)];
                    }
                }
                // un sous-menu n'est affiché que s'il contient au moins un lien autorisé
                if (! array_filter($entry['sub'], static fn (array $s): bool => isset($s['href']))) {
                    $entry['sub'] = [];
                }
                $entries[] = $entry;
            }
        } else { // 'link' (barre latérale) et 'dropdown' : liste de liens ou de séparateurs
            foreach ($def->items as $element) {
                if ($element->type === 'divider') {
                    $entries[] = ['divider' => true];
                } elseif ($element->type === 'link' && $acl->hasAccess(strtolower($element->url))) {
                    $visible   = true;
                    $entries[] = [
                        'href'  => base_url($element->url),
                        'label' => tr($element->name),
                        'icon'  => $element->icon ?? '',
                    ];
                }
            }
        }

        return $this->resolved = [
            'type'    => $def->type ?? '',
            'icon'    => $def->icon ?? '',
            'entries' => $entries,
            'visible' => $visible,
        ];
    }
}
