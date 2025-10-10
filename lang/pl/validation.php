<?php
// lang/pl/validation.php
return [
    'required' => 'Pole :attribute jest wymagane.',
    'email'    => 'Pole :attribute musi być prawidłowym adresem e-mail.',
    'unique'   => 'Pole :attribute musi być unikalne.',
    'min' => [
        'string' => 'Pole :attribute musi mieć co najmniej :min znaków.',
    ],
    'confirmed' => 'Potwierdzenie pola :attribute nie zgadza się.',

    'attributes' => [
        'name'                  => 'imię i nazwisko',
        'email'                 => 'adres e-mail',
        'album'                 => 'numer albumu',
        'password'              => 'hasło',
        'password_confirmation' => 'potwierdzenie hasła',
    ],
];
