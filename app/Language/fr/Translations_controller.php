<?php

// =====================================================================
// Traductions pour Translations_controller (éditeur de traductions)
// NB : la clé "Translations_controller" (titre dans optionmenu) est
//      dans menu_lang.php.
// =====================================================================

// CRUD générique (pour cohérence avec les autres contrôleurs)

return [
    'GESTION_Translations_controller' => 'Gestion des traductions',
    'Translations_controller_subtitle' => 'Editer les fichiers de langue du site',
    'Translations_controller_list' => 'Traductions',
    'Translations_controller_edit' => 'Edition d\'un fichier de langue',
    'TRANSLATIONS_PICK_LANGUAGE' => 'Choisir une langue à éditer',
    'TRANSLATIONS_PICK_LANGUAGE_HELP' => 'Selectionnez la langue dont vous voulez modifier les fichiers de traduction. Ce choix n\'affecte PAS la langue d\'affichage du site (utilisez le menu en haut à droite pour cela).',
    'TRANSLATIONS_LANGUAGE' => 'Langue',
    'TRANSLATIONS_REFERENCE' => 'référence',
    'TRANSLATIONS_FILES_FOR' => 'Fichiers de traduction pour %s',
    'TRANSLATIONS_NO_FILES' => 'Aucun fichier de langue trouvé.',
    'TRANSLATIONS_FILE' => 'Fichier',
    'TRANSLATIONS_FILE_MISSING' => 'fichier absent',
    'TRANSLATIONS_COVERAGE' => 'Couverture',
    'TRANSLATIONS_KEYS_REF' => 'Clés (réf.)',
    'TRANSLATIONS_KEYS_PRESENT' => 'Présentes',
    'TRANSLATIONS_KEYS_MISSING' => 'Manquantes',
    'TRANSLATIONS_KEYS_EXTRA' => 'En trop',
    'TRANSLATIONS_EDIT' => 'Editer',
    'TRANSLATIONS_ADD_LANGUAGE' => 'Ajouter une nouvelle langue',
    'TRANSLATIONS_NEW_LANGUAGE_NAME' => 'Nom de la langue (en anglais, sans accent)',
    'TRANSLATIONS_CREATE' => 'Créer',
    'TRANSLATIONS_ADD_LANGUAGE_HELP' => 'Le nom doit être en minuscules, sans espace ni accent (ex: english, german, italian). Tous les fichiers de la langue de référence (français) seront copiés comme point de départ.',
    'TRANSLATIONS_ADD_LANGUAGE_CONFIRM' => 'Créer une nouvelle langue va dupliquer l\'intégralité des fichiers français. Continuer ?',
    'TRANSLATIONS_LANGUAGE_EXISTS' => 'La langue "%s" existe déjà.',
    'TRANSLATIONS_LANGUAGE_CREATED' => 'Langue "%s" créée avec succès.',
    'TRANSLATIONS_INVALID_LANGUAGE_NAME' => 'Nom de langue invalide. Utilisez uniquement des lettres minuscules.',
    'TRANSLATIONS_MKDIR_ERROR' => 'Impossible de créer le répertoire de la langue. Vérifiez les permissions sur application/language/.',
    'TRANSLATIONS_EDITING' => 'Édition',
    'TRANSLATIONS_BACK_TO_LIST' => 'Retour à la liste',
    'TRANSLATIONS_KEYS_COUNT' => '%d clé(s) au total dans ce fichier (référence + cible)',
    'TRANSLATIONS_KEY' => 'Clé',
    'TRANSLATIONS_VALUE' => 'Valeur',
    'TRANSLATIONS_FILTER_PLACEHOLDER' => 'Filtrer (clé ou texte)...',
    'TRANSLATIONS_ONLY_MISSING' => 'Uniquement les clés manquantes',
    'TRANSLATIONS_NOT_IN_REF' => '(absente de la référence)',
    'TRANSLATIONS_MISSING' => 'manquante',
    'TRANSLATIONS_EXTRA' => 'en trop',
    'TRANSLATIONS_SAVE' => 'Enregistrer',
    'TRANSLATIONS_FILE_DOES_NOT_EXIST_YET' => 'Ce fichier n\'existe pas encore dans cette langue. En enregistrant, il sera créé à partir de la structure du fichier de référence (français), avec les valeurs que vous saisissez.',
    'TRANSLATIONS_ADD_NEW_KEY' => 'Ajouter une nouvelle clé à ce fichier',
    'TRANSLATIONS_ADD_KEY_HELP' => 'La clé doit être unique. Lettres, chiffres, tirets et underscores uniquement. La nouvelle clé sera ajoutée à la fin du fichier.',
    'TRANSLATIONS_DELETE_KEY' => 'Supprimer cette clé',
    'TRANSLATIONS_DELETE_KEY_CONFIRM' => 'Supprimer définitivement cette clé du fichier ? Une sauvegarde .bak sera créée.',
    'TRANSLATIONS_KEY_DELETED' => 'Clé "%s" supprimée.',
    'TRANSLATIONS_KEY_NOT_FOUND' => 'Clé "%s" introuvable dans le fichier.',
    'TRANSLATIONS_INVALID_KEY' => 'Le nom de clé contient des caractères non autorisés. Lettres, chiffres, tirets et underscores uniquement.',
    'TRANSLATIONS_SAVED_OK' => 'Fichier de traduction enregistré. Une sauvegarde .bak a été créée.',
    'TRANSLATIONS_WRITE_ERROR' => 'Impossible d\'écrire le fichier. Vérifiez les permissions sur :',
    'LANG_SWITCHER_TITLE' => 'Changer de langue',
];
