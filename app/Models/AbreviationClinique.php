<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Abréviations du guide clinique (E. = enfant, dlr = douleur…), affichées en info-bulle. */
class AbreviationClinique extends Model
{
    protected $table = 'abreviations_cliniques';

    protected $fillable = ['abreviation', 'signification'];

    /**
     * Texte échappé dont les abréviations sont entourées d'un <abbr title="…">.
     * Seules les abréviations contenant au moins une lettre sont annotées.
     */
    public static function annoter(?string $texte): string
    {
        static $motif = null, $sens = [];

        $html = e((string) $texte);
        if ($motif === null) {
            $liste = static::all()->filter(fn($a) => preg_match('/\p{L}/u', $a->abreviation))->sortByDesc(fn($a) => mb_strlen($a->abreviation));
            foreach ($liste as $a) {
                $sens[e($a->abreviation)] = $a->signification;
            }
            $motif = $sens ? '/(?<![\p{L}\p{N}])(' . implode('|', array_map(fn($a) => preg_quote($a, '/'), array_keys($sens))) . ')(?![\p{L}\p{N}])/u' : '';
        }

        return $motif ? preg_replace_callback($motif, fn($m) => '<abbr title="' . e($sens[$m[1]]) . '">' . $m[1] . '</abbr>', $html) : $html;
    }
}
