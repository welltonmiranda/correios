<?php

namespace WelltonMiranda\Correios;

abstract class WebService
{
    /**
     * URL do SIGEP webservice dos Correios.
     */
    const SIGEP = 'https://apps.correios.com.br/SigepMasterJPA/AtendeClienteService/AtendeCliente';

    /**
     * URL do webservice dos Correios para calculo de preços e prazos.
     */
    const CALC_PRICE_DEADLINE = 'http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo';

     /**
     * URL do webservice dos Correios para rastreito de objetos.
     */
    const RASTREAR = 'https://www2.correios.com.br/sistemas/rastreamento/resultado.cfm';
    
}