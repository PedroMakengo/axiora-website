<?php

namespace Core;

// =====================================================================
// Core\Validador — regras de validação reutilizadas por vários
// controladores (registo, redefinição e alteração de senha, formulários).
// =====================================================================

class Validador
{
    public const SENHA_MINIMO = 8;
    public const SENHA_MAXIMO = 72; // limite do bcrypt

    /** Senhas demasiado comuns, recusadas mesmo cumprindo as regras. */
    private const SENHAS_COMUNS = [
        'password1', 'password123', 'senha123', 'senha1234', 'abc12345', 'qwerty123',
        'admin123', 'admin1234', 'angola123', 'luanda123', '12345678a', 'a12345678',
        'iloveyou1', 'welcome1', 'teste123', 'teste1234',
    ];

    /**
     * Senha forte o suficiente: 8 a 72 caracteres, combinando letras e números,
     * não trivial e (se indicado) sem conter a parte local do email.
     */
    public static function senhaForte(string $senha, string $email = ''): bool
    {
        return self::erroSenha($senha, $email) === null;
    }

    /** Mensagem de erro da senha, ou null se for válida. */
    public static function erroSenha(string $senha, string $email = ''): ?string
    {
        if (strlen($senha) < self::SENHA_MINIMO) {
            return 'A senha deve ter pelo menos ' . self::SENHA_MINIMO . ' caracteres.';
        }
        if (strlen($senha) > self::SENHA_MAXIMO) {
            return 'A senha não pode ter mais de ' . self::SENHA_MAXIMO . ' caracteres.';
        }
        if (!preg_match('/[A-Za-z]/', $senha) || !preg_match('/[0-9]/', $senha)) {
            return 'A senha deve combinar letras e números.';
        }
        if (in_array(strtolower($senha), self::SENHAS_COMUNS, true)) {
            return 'Esta senha é demasiado comum. Escolha outra.';
        }
        $local = strtolower(strstr($email, '@', true) ?: '');
        if (strlen($local) >= 4 && str_contains(strtolower($senha), $local)) {
            return 'A senha não pode conter o seu email.';
        }
        return null;
    }

    /** Texto limpo (trim) e cortado ao tamanho máximo da coluna. */
    public static function texto($valor, int $maximo): string
    {
        // Bytes que não são UTF-8 válido (ex.: texto colado de outra codificação) são descartados,
        // em vez de fazerem o MySQL recusar a gravação.
        $texto = is_string($valor) ? mb_convert_encoding($valor, 'UTF-8', 'UTF-8') : '';
        return mb_substr(trim($texto), 0, $maximo);
    }

    /** Email válido e com tamanho aceitável. */
    public static function email(string $email): bool
    {
        return mb_strlen($email) <= 150 && filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /** Telefone opcional: dígitos, espaços, +, -, (), entre 6 e 20 caracteres. */
    public static function telefone(string $telefone): bool
    {
        return $telefone === '' || (bool) preg_match('/^\+?[0-9\s\-()]{6,20}$/', $telefone);
    }

    /** Data no formato AAAA-MM-DD e existente no calendário. */
    public static function data(string $data): bool
    {
        if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $data, $m)) {
            return false;
        }
        return checkdate((int) $m[2], (int) $m[3], (int) $m[1]);
    }

    /** Data/hora de um input datetime-local (AAAA-MM-DDTHH:MM[:SS]). */
    public static function dataHora(string $valor): bool
    {
        return (bool) preg_match('/^\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}(:\d{2})?$/', $valor)
            && self::data(substr($valor, 0, 10));
    }
}
