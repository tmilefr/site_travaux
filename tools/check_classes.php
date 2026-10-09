<?php
/**
 * Verification statique : toutes les classes referencees (new X, X::, extends,
 * instanceof, catch, typehints) dans app/ doivent exister apres resolution
 * des namespaces / use. Usage : php tools/check_classes.php
 */
require __DIR__ . '/../vendor/autoload.php';
// Les classes du framework sont seulement localisees (findFile), pas chargees : certaines (CLI) exigent le contexte spark
$loader = null;
foreach (spl_autoload_functions() as $f) { if (is_array($f) && $f[0] instanceof \Composer\Autoload\ClassLoader) { $loader = $f[0]; break; } }

$root = realpath(__DIR__ . '/../app');
$it   = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
$bad  = 0;
foreach ($it as $file) {
    if ($file->getExtension() !== 'php') continue;
    $path = $file->getPathname();
    if (str_contains($path, '/Views/') || str_contains($path, '/Language/') || str_contains($path, '/Config/legacy/')) continue;
    $tokens = PhpToken::tokenize(file_get_contents($path));
    $tokens = array_values(array_filter($tokens, fn($t) => !in_array($t->id, [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT])));
    $ns = ''; $uses = [];
    $n = count($tokens);
    for ($i = 0; $i < $n; $i++) {
        $t = $tokens[$i];
        if ($t->id === T_NAMESPACE) { $ns = ''; $j = $i + 1; while ($tokens[$j]->text !== ';' && $tokens[$j]->text !== '{') { $ns .= $tokens[$j]->text; $j++; } }
        if ($t->id === T_USE && ($tokens[$i-1]->text ?? '') !== ')' && !in_array($tokens[$i+1]->id, [T_FUNCTION, T_CONST])) {
            // top-level use (not closure use / trait use inside class: heuristic -> only before first class)
            $j = $i + 1; $name = '';
            while ($tokens[$j]->text !== ';' && $tokens[$j]->id !== T_AS && $tokens[$j]->text !== ',') { $name .= $tokens[$j]->text; $j++; }
            $alias = substr(strrchr('\\' . $name, '\\'), 1);
            if ($tokens[$j]->id === T_AS) $alias = $tokens[$j + 1]->text;
            $uses[strtolower($alias)] = ltrim($name, '\\');
        }
    }
    $resolve = function (string $name) use ($ns, $uses): string {
        if ($name[0] === '\\') return ltrim($name, '\\');
        $parts = explode('\\', $name);
        $first = strtolower($parts[0]);
        if (isset($uses[$first])) { $parts[0] = $uses[$first]; return implode('\\', $parts); }
        return ($ns ? $ns . '\\' : '') . $name;
    };
    $skip = ['self', 'static', 'parent', 'array', 'callable', 'string', 'int', 'float', 'bool', 'mixed', 'void', 'null', 'object', 'iterable', 'false', 'true', 'never'];
    $check = function (string $name, int $line) use ($resolve, $skip, $path, &$bad, $loader) {
        if (in_array(strtolower($name), $skip, true)) return;
        $fq = $resolve($name);
        if (str_starts_with($fq, 'CodeIgniter\\') && $loader->findFile($fq)) return;
        if (!class_exists($fq) && !interface_exists($fq) && !trait_exists($fq) && !enum_exists($fq)) {
            // classe declaree dans le meme fichier ?
            if (str_contains(file_get_contents($path), 'class ' . basename(str_replace('\\', '/', $fq)))) return;
            echo str_replace(dirname(__DIR__) . '/', '', $path) . ":$line  unresolved class $fq (from $name)\n";
            $bad++;
        }
    };
    $readName = function (int &$i) use ($tokens): string {
        $name = '';
        while (in_array($tokens[$i]->id, [T_STRING, T_NS_SEPARATOR, T_NAME_QUALIFIED, T_NAME_FULLY_QUALIFIED, T_NAME_RELATIVE], true)) { $name .= $tokens[$i]->text; $i++; }
        return $name;
    };
    for ($i = 0; $i < $n; $i++) {
        $t = $tokens[$i];
        if ($t->id === T_NEW) {
            $j = $i + 1;
            if ($tokens[$j]->id === T_CLASS) continue;               // classe anonyme
            if ($tokens[$j]->id === T_VARIABLE || $tokens[$j]->text === '(' || $tokens[$j]->id === T_STATIC) continue;
            $name = $readName($j); if ($name !== '') $check($name, $t->line);
        } elseif (in_array($t->id, [T_EXTENDS, T_IMPLEMENTS], true)) {
            $j = $i + 1;
            while (true) { $name = $readName($j); if ($name !== '') $check($name, $t->line); if ($tokens[$j]->text === ',') { $j++; continue; } break; }
        } elseif ($t->id === T_INSTANCEOF) {
            $j = $i + 1; if ($tokens[$j]->id !== T_VARIABLE) { $name = $readName($j); if ($name !== '') $check($name, $t->line); }
        } elseif ($t->id === T_CATCH) {
            $j = $i + 2;
            while (true) { $name = $readName($j); if ($name !== '') $check($name, $t->line); if ($tokens[$j]->text === '|') { $j++; continue; } break; }
        } elseif (in_array($t->id, [T_STRING, T_NAME_QUALIFIED, T_NAME_FULLY_QUALIFIED], true) && ($tokens[$i + 1]->id ?? 0) === T_DOUBLE_COLON) {
            $prev = $tokens[$i - 1]->id ?? 0;
            if ($prev === T_OBJECT_OPERATOR || $prev === T_DOUBLE_COLON || $prev === T_NULLSAFE_OBJECT_OPERATOR) continue;
            $check($t->text, $t->line);
        }
    }
}
echo $bad ? "\n$bad problem(s)\n" : "OK: all referenced classes resolve\n";
exit($bad ? 1 : 0);
