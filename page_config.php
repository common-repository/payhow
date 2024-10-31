<?php     require_once __DIR__ . "/Payhow.php";
    $payhow = new Payhow;
    $status = $payhow->statusIntegration();
?>
<div class="ph_main">
    <div class="ph_content">
        <div class="ph_header">
            <?php
            $logo_src = plugin_dir_url( __FILE__ ) . 'images/payhow_logo.png';
            ?>
            <img class="logo" src="<?php echo $logo_src ?>" alt="Logo Payhow">
        </div>

        <div class="ph_body">
        <?php 
            if($status){ ?>
            <div class="ph_box_status">
                <div class="status_header">
                    <h2><span class="icon-status icon-success">
                        </span>Status: Online</h2>
                </div>
                <div class="status_body">
                    <p>Você está conectado aos nossos servidores e pronto para utilizar nossas ferramentas de checkout</p>
                </div>
                <div class="status_footer">
                    <button data-js="update_status">
                        <span class="icon-status icon-refresh">
                        </span>
                        Atualizar
                    </button>
                </div>
            </div>

        <?php
            }else{ ?>

            <div class="ph_box_status">
                <div class="status_header">
                    <h2><span class="icon-status icon-fail">
                        </span>Status: Offline</h2>
                </div>
                <div class="status_body">
                    <p>Ops... você ainda não está conectado! <br>
                    Por favor verifique o cadastro da loja na plataforma Payhow (<a href="https://payhow.com.br/ecommerce" target="_blank">www.payhow.com.br/ecommerce</a>) ou entre em contato através do nosso
                        <a href="https://lsbr-portalcliente.atlassian.net/servicedesk/customer/portal/6" target="_blank">suporte técnico</a>
                    </p>
                </div>
                <div class="status_footer">
                    <button data-js="update_status">
                        <span class="icon-status icon-refresh">
                        </span>
                        Atualizar
                    </button>
                </div>
            </div>
        <?php
            }
        ?>
        </div>
    </div>

</div>

<script>
    document.querySelector('[data-js="update_status"]').addEventListener('click',function () {
        window.location.reload()
    })
</script>

