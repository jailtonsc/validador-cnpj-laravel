<?php

namespace ValidadorCnpj\Validation;

use Illuminate\Container\Container;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\Validator as IlluminateValidator;

/**
 * Class Cpf
 * @package Util\Services\Validation
 */
class CnpjValidation extends IlluminateValidator
{

    /**
     * @var array
     */
    private $_custom_messages;

    /**
     * @param \Symfony\Component\Translation\TranslatorInterface $translator
     * @param array $data
     * @param array $rules
     * @param array $messages
     * @param array $customAttributes
     */
    public function __construct($translator, $data, $rules, $messages = array(), $customAttributes = array())
    {
        parent::__construct($translator, $data, $rules, $messages, $customAttributes);

        $this->setMessage();
        $this->_set_custom_stuff();
    }

    /**
     * @return mixed
     */
    public function app()
    {
        return Container::getInstance()->make('config');
    }

    /**
     * @return void
     */
    public function setMessage()
    {
        $this->_custom_messages = ['cnpj' =>  $this->app()->get('cnpj.message')];
    }


    /**
     * Setup any customizations etc
     *
     * @return void
     */
    protected function _set_custom_stuff()
    {
        $this->setCustomMessages($this->_custom_messages);
    }

    public function multiplicaCnpj($cnpj, $posicao = 5 )
    {
        // Variável para o cálculo
        $calculo = 0;

        // Laço para percorrer os item do cnpj
        for ( $i = 0; $i < strlen( $cnpj ); $i++ ) {
            // Cálculo mais posição do CNPJ * a posição
            $calculo = $calculo + ( $cnpj[$i] * $posicao );

            // Decrementa a posição a cada volta do laço
            $posicao--;

            // Se a posição for menor que 2, ela se torna 9
            if ( $posicao < 2 ) {
                $posicao = 9;
            }
        }
        // Retorna o cálculo
        return $calculo;
    }

    public function validateCnpj($attribute, $value)
    {
        // Deixa o CNPJ com apenas números
        $cnpj = preg_replace( '/[^0-9]/', '', $value);

        // Garante que o CNPJ é uma string
        $cnpj = (string)$cnpj;

        // O valor original
        $cnpjOriginal = $cnpj;

        // Captura os primeiros 12 números do CNPJ
        $primeirosNumerosCnpj = substr($cnpj, 0, 12 );

        // Faz o primeiro cálculo
        $primeiroCalculo = $this->multiplicaCnpj($primeirosNumerosCnpj);

        // Se o resto da divisão entre o primeiro cálculo e 11 for menor que 2, o primeiro
        // Dígito é zero (0), caso contrário é 11 - o resto da divisão entre o cálculo e 11
        $primeiroDigito = ( $primeiroCalculo % 11 ) < 2 ? 0 :  11 - ( $primeiroCalculo % 11 );

        // Concatena o primeiro dígito nos 12 primeiros números do CNPJ
        // Agora temos 13 números aqui
        $primeirosNumerosCnpj .= $primeiroDigito;

        // O segundo cálculo é a mesma coisa do primeiro, porém, começa na posição 6
        $segundoCalculo = $this->multiplicaCnpj($primeirosNumerosCnpj, 6 );
        $segundoDigito = ( $segundoCalculo % 11 ) < 2 ? 0 :  11 - ( $segundoCalculo % 11 );

        // Concatena o segundo dígito ao CNPJ
        $cnpj = $primeirosNumerosCnpj . $segundoDigito;

        // Verifica se o CNPJ gerado é idêntico ao enviado
        if ( $cnpj === $cnpjOriginal ) {
            return true;
        }
        return false;
    }
}
