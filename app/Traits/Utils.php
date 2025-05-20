<?php

namespace App\Traits;

trait Utils
{

        public function Encrypt($text)
        {

                $plaintext = $text; // 'LUIS fax';
                $password = 'luisfax';
                // CBC has an IV and thus needs randomness every time a message is encrypted
                $method = 'aes-256-cbc';

                // Password length must be 32 characters (256 bit)
                $key = substr(hash('sha256', $password, true), 0, 32);
                //echo "Password:" . $password . "<br>";

                // The most secure password
                //$key = openssl_random_pseudo_bytes(openssl_cipher_iv_length($method));

                // IV must be 16 characters (128 bit)
                $iv = chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0);

                // The safest IV
                // Do not use in actual applications iv=0, the following IV should be used:
                // $ivlen = openssl_cipher_iv_length($method);
                // $iv = openssl_random_pseudo_bytes($ivlen);
                // encryption
                $encrypted = base64_encode(openssl_encrypt($plaintext, $method, $key, OPENSSL_RAW_DATA, $iv));

                // decrypt
                $decrypted = openssl_decrypt(base64_decode($encrypted), $method, $key, OPENSSL_RAW_DATA, $iv);

                $r = 'plaintext=' . $plaintext . "<br>";
                $r = $r . 'cipher=' . $method . "<br>";
                $r = $r . 'encrypted to: ' . $encrypted . "<br>";
                $r = $r . 'decrypted to: ' . $decrypted . "<br><br>";

                return $encrypted;
        }


        public function getCodigoBarras()
        {
                // Año actual (4 dígitos)
                $anio = date('Y');

                // Día juliano (001 al 365/366)
                $diaJuliano = str_pad(date('z') + 1, 3, '0', STR_PAD_LEFT);

                // Ruta del archivo que guarda la secuencia (dentro de storage/app)
                $rutaArchivo = storage_path('app/ultima_secuencia.txt');

                // Leer o inicializar secuencia
                if (file_exists($rutaArchivo)) {
                        $ultimaSecuencia = (int)file_get_contents($rutaArchivo);
                } else {
                        $ultimaSecuencia = 0;
                }

                // Incrementar secuencia
                $nuevaSecuencia = $ultimaSecuencia + 1;

                // Guardar nueva secuencia
                file_put_contents($rutaArchivo, $nuevaSecuencia);

                // Asegurar formato con al menos 2 dígitos (01, 02, ..., 100, etc.)
                $secuenciaFormateada = str_pad($nuevaSecuencia, 2, '0', STR_PAD_LEFT);

                // Generar código de barras final
                $codigoBarras = $anio . $diaJuliano . $secuenciaFormateada;

                return $codigoBarras;
        }
}
