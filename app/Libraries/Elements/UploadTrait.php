<?php

namespace App\Libraries\Elements;

use CodeIgniter\HTTP\Files\UploadedFile;

/**
 * Réception d'un fichier envoyé par un champ de formulaire (éléments `file` et `img`).
 *
 * Seules les extensions de la liste blanche sont acceptées, le nom est assaini et le fichier est
 * déplacé avec UploadedFile::move(). En cas de refus, le message est mis en flashdata
 * (`upload_error`, affiché par le gabarit) et la valeur précédente du champ est conservée.
 */
trait UploadTrait
{
    /**
     * Enregistre le fichier du champ et retourne son nom, ou la valeur déjà mémorisée si rien n'est envoyé ou refusé.
     *
     * @param list<string> $allowed extensions acceptées (minuscules)
     */
    protected function receiveUpload(array $allowed, string $prefix = ''): ?string
    {
        $request = service('request');
        $memory  = $request->getPost('memory_' . $this->name);
        $memory  = $memory === null ? null : basename((string) $memory); // valeur venue du navigateur : jamais de chemin
        $file    = $request->getFile($this->name);

        if ($file === null || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return $memory;
        }
        if (! $file->isValid() || $file->hasMoved()) {
            return $this->refuseUpload($file->getErrorString(), $memory);
        }

        $extension = strtolower($file->getClientExtension());
        if (! in_array($extension, $allowed, true)) {
            return $this->refuseUpload('Type de fichier refusé (.' . $extension . ') : ' . implode(', ', $allowed), $memory);
        }

        $directory = rtrim(ROOTPATH . $this->path, '/\\');
        if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
            return $this->refuseUpload('Dossier de destination indisponible.', $memory);
        }

        $name = $prefix . $this->safeName($file);
        if (is_file($directory . '/' . $name)) {
            $name = date('YmdHis') . '_' . $name; // ne jamais écraser un fichier existant
        }

        try {
            $file->move($directory, $name);
        } catch (\Throwable $e) {
            log_message('error', 'Upload {name} : {message}', ['name' => $this->name, 'message' => $e->getMessage()]);

            return $this->refuseUpload("Le fichier n'a pas pu être enregistré.", $memory);
        }

        return $name;
    }

    /** Nom d'origine réduit à lettres, chiffres, point, tiret et souligné. */
    private function safeName(UploadedFile $file): string
    {
        $base = pathinfo($file->getClientName(), PATHINFO_FILENAME);
        $base = trim((string) preg_replace('/[^A-Za-z0-9_-]+/', '_', transliterator_transliterate('Any-Latin; Latin-ASCII', $base) ?: $base), '_');

        return ($base !== '' ? $base : 'fichier') . '.' . strtolower($file->getClientExtension());
    }

    private function refuseUpload(string $message, ?string $memory): ?string
    {
        session()->setFlashdata('upload_error', $message);

        return $memory;
    }
}
