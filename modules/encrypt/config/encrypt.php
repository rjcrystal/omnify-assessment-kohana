<?php

return [

	'default' => [
		/**
		 * The following options must be set:
		 *
		 * string   key     secret passphrase
		 * integer  mode    encryption mode, one of MCRYPT_MODE_*
		 * integer  cipher  encryption cipher, one of the Mcrpyt cipher constants
		 */
        'type'   => 'openssl',
        'cipher' => 'AES-256-CBC',
		'mode' => 'MCRYPT_MODE_NOFB',
        'key'=>getenv('ENCRYPTION_KEY')
	],

];
