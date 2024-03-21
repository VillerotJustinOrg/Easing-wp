<?php

// Get the current directory
$currentDirectory = dirname(__FILE__);

// Get just the directory name
$directoryName = basename($currentDirectory);

// Output the directory name
error_log("Current directory name: " . $directoryName);



// DOT ENV
// Define the file path
$dotenvFilePath = __DIR__ . '/.env';

// Check if the file exists
if (file_exists($dotenvFilePath)) {
    // Attempt to open the file for reading
    $file = @fopen($dotenvFilePath, "r");

    // Check if the file is opened successfully
    if ($file) {
        // Read the file line by line until the end of the file
        while (($line = fgets($file)) !== false) {
            // Remove any trailing newline characters
            $line = rtrim($line, "\r\n");

            // Set the environment variable
            putenv($line);
        }

        // Close the file
        fclose($file);

        // Log success message
        error_log("DOTENV Loaded");
    } else {
        // Log error message if unable to open the file
        error_log("Error: Unable to open the .env file.");
    }
} else {
    // Log error message if the .env file does not exist
    error_log("Error: .env file not found.");
}


//error_log(print_r(getenv(), true));
//
//error_log("Login: ".getenv('LOGIN'));
//error_log("Password: ".getenv('PASSWD'));
//error_log("URL: ".getenv('API_URL'));


/**
 * La configuration de base de votre installation WordPress.
 *
 * Ce fichier contient les réglages de configuration suivants : réglages MySQL,
 * préfixe de table, clés secrètes, langue utilisée, et ABSPATH.
 * Vous pouvez en savoir plus à leur sujet en allant sur
 * {@link https://fr.wordpress.org/support/article/editing-wp-config-php/ Modifier
 * wp-config.php}. C’est votre hébergeur qui doit vous donner vos
 * codes MySQL.
 *
 * Ce fichier est utilisé par le script de création de wp-config.php pendant
 * le processus d’installation. Vous n’avez pas à utiliser le site web, vous
 * pouvez simplement renommer ce fichier en "wp-config.php" et remplir les
 * valeurs.
 *
 * @link https://fr.wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Réglages MySQL - Votre hébergeur doit vous fournir ces informations. ** //
/** Nom de la base de données de WordPress. */
define( 'DB_NAME', 'easing' );

/** Utilisateur de la base de données MySQL. */
define( 'DB_USER', 'admin' );

/** Mot de passe de la base de données MySQL. */
define( 'DB_PASSWORD', 'admin' );

/** Adresse de l’hébergement MySQL. */
define( 'DB_HOST', 'localhost' );

/** Jeu de caractères à utiliser par la base de données lors de la création des tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** Type de collation de la base de données.
  * N’y touchez que si vous savez ce que vous faites.
  */
define('DB_COLLATE', '');

/**#@+
 * Clés uniques d’authentification et salage.
 *
 * Remplacez les valeurs par défaut par des phrases uniques !
 * Vous pouvez générer des phrases aléatoires en utilisant
 * {@link https://api.wordpress.org/secret-key/1.1/salt/ le service de clés secrètes de WordPress.org}.
 * Vous pouvez modifier ces phrases à n’importe quel moment, afin d’invalider tous les cookies existants.
 * Cela forcera également tous les utilisateurs à se reconnecter.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'gc:e$5dr0v5U5|Vyt^5CgxJ5x$DbS)6 Fc&XhjIP$`Z(-V)5C-,}**N$>hFK`_8:' );
define( 'SECURE_AUTH_KEY',  'SVJu Y^l:^Km2W{J:=rT.H53-o7^T`NB^h=g6Gbc=dwo}-r}z3szffr{y|t1^Bq]' );
define( 'LOGGED_IN_KEY',    'zduK6@DUz1?X->h35Y^*[W/rH;co0!o,oFE(FE!4Jx6ai{bnB470s@t>+(^Q` *+' );
define( 'NONCE_KEY',        'ODWo74jyjLl<KW_h.^0sr_xa@Twi=&Ud+u@X0dYDEe=$Xk}?#Qzyq<>wf*B3f?/>' );
define( 'AUTH_SALT',        '4M6)>TO?LAdXUTTL4dzxxX?0(V;4GM{f=*(kb4W6P]jWv 8XZ!&CK,.4p.2n 9UR' );
define( 'SECURE_AUTH_SALT', '%T(}E@aW jJTu02P8-VA$BB*x?QoJZ>rUOF5>QoZ:N!&h2*H.5s6aClM/nP<p|+*' );
define( 'LOGGED_IN_SALT',   'X{dzAW+f2J}p}5+8wS)(Tp6A3Qo-WK&6(PU~jAMqi6AJEbN>y23mTFb7/vBmK+4H' );
define( 'NONCE_SALT',       '8y6b(l?Xhu51)TA4O>X)$6Le;CjpP0X8x_i-p[/b=f_pbmvKz7O[f)e3x5mS<{R@' );
/**#@-*/

/**
 * Préfixe de base de données pour les tables de WordPress.
 *
 * Vous pouvez installer plusieurs WordPress sur une seule base de données
 * si vous leur donnez chacune un préfixe unique.
 * N’utilisez que des chiffres, des lettres non-accentuées, et des caractères soulignés !
 */
$table_prefix = 'wp_';

/**
 * Pour les développeurs et développeuses : le mode déboguage de WordPress.
 *
 * En passant la valeur suivante à "true", vous activez l’affichage des
 * notifications d’erreurs pendant vos essais.
 * Il est fortement recommandé que les développeurs et développeuses d’extensions et
 * de thèmes se servent de WP_DEBUG dans leur environnement de
 * développement.
 *
 * Pour plus d’information sur les autres constantes qui peuvent être utilisées
 * pour le déboguage, rendez-vous sur la documentation.
 *
 * @link https://fr.wordpress.org/support/article/debugging-in-wordpress/
 */
define('WP_DEBUG', false);

/* C’est tout, ne touchez pas à ce qui suit ! Bonne publication. */

/** Chemin absolu vers le dossier de WordPress. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Réglage des variables de WordPress et de ses fichiers inclus. */
require_once(ABSPATH . 'wp-settings.php');

