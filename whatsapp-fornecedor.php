<?php
/*
Plugin Name: WhatsApp Fornecedor
Description: Envia mensagens no WhatsApp para fornecedores quando um pedido é feito no WooCommerce.
Version: 1.0
Author: Seu Nome
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Hook para acionar a função quando o pedido é concluído
add_action('woocommerce_thankyou', 'enviar_mensagem_fornecedor_whatsapp', 10, 1);

function enviar_mensagem_fornecedor_whatsapp($order_id)
{
    if (!$order_id) {
        return;
    }

    $order = wc_get_order($order_id);

    if (!$order) {
        return;
    }

    $order_items = $order->get_items();
    $soma = 0;
    $Produtos = "";

    foreach ($order_items as $item_id => $item) {
        $product = $item->get_product();
        $Produtos .= "*" . $item->get_quantity() . "x* " . $item->get_name() . "%0a%0a";
        $soma += $item->get_total();
    }

    foreach ($order->get_meta_data() as $valor) {
        if ($valor->key == "_billing_numero") {
            $numero = $valor->value;
        }

        if ($valor->key == "_billing_complemento") {
            $complemento = $valor->value;
        }

        if ($valor->key == "_billing_pagamento") {
            $pagamento = $valor->value;
        }

        if ($valor->key == "_billing_observacoes") {
            $obs = $valor->value;
        }
    }

    $dados = "*COMPRA EFETUADA*%0a*";
    $dados .= "----------------------------------------%0a";
    $dados .= "*RESUMO DO COMPRA*%0a%0a";
    $dados .= "Cód: " . $order->get_id() . "%0a%0a";
    $dados .= "*PRODUTOS*%0a%0a";
    $dados .= $Produtos;
    $dados .= "----------------------------------------%0a";
    $dados .= "*SUBTOTAL* R$" . number_format($soma, 2, ',', '.') . "%0a%0a";
    $dados .= "----------------------------------------%0a";
    $dados .= "*DADOS DO CLIENTE%0a*";
    $dados .= "*Nome:* " . $order->get_billing_first_name() . " " . $order->get_billing_last_name() . "%0a";
    $dados .= "*Endereço:* " . $order->get_billing_address_1() . " , " . $numero . " %0a";
    $dados .= "*Cep:* " . $order->get_billing_postcode() . " %0a";
    $dados .= "*Cidade:* " . $order->get_billing_city() . " %0a";
    $dados .= "*Bairro:* " . $order->get_billing_address_2() . " %0a";
    $dados .= "*Complemento:* " . $complemento . " %0a";
    $dados .= "*Telefone/WhatsApp* " . $order->get_billing_phone() . " %0a";
    $dados .= "*TOTAL:* = R$ " . number_format($order->get_total(), 2, ",", ".") . " %0a";
    $dados .= "*Pagamento* " . $pagamento . " %0a";
    $dados .= "*Observações:* " . $obs . " %0a";

    foreach ($order_items as $item) {
        $product = $item->get_product();
        $fornecedor_telefone = get_post_meta($product->get_id(), 'fornecedor_telefone', true);

        if ($fornecedor_telefone) {
            $telefone = $fornecedor_telefone;
            $whatsapp_url = "https://api.whatsapp.com/send?phone=" . $telefone . "&text=" . urlencode($dados);

            // Use wp_remote_get para enviar a mensagem em vez de redirecionar
            wp_remote_get($whatsapp_url);
        }
    }
}
