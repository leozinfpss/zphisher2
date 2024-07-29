function checkPaymentStatus(transaction_id) {
    if (!transaction_id) {
        console.error("Transaction ID is undefined");
        return;
    }

    console.log("Verificando status de pagamento para a transação:", transaction_id); // Log para cada verificação

    $.ajax({
        type: 'POST',
        url: 'check_payment_status.php',
        data: { transaction_id: transaction_id },
        success: function(response) {
            console.log("Resposta recebida para a transação:", transaction_id, response); // Log da resposta recebida

            try {
                var data = JSON.parse(response);
            } catch (e) {
                console.error("Erro ao analisar a resposta JSON:", e);
                return;
            }

            if (data.status === 'confirmed') {
                console.log("Pagamento confirmado para a transação:", transaction_id); // Log de confirmação
                document.getElementById('qrCodeModal').classList.remove('modalDeposit-show');
                Toastify({
                    text: "Pagamento confirmado com sucesso!",
                    duration: 3000,
                    close: true,
                    gravity: "top", // `top` or `bottom`
                    position: "center", // `left`, `center` or `right`
                    backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)",
                }).showToast();
                clearInterval(paymentCheckInterval); // Parar a verificação após a confirmação
            } else if (data.status === 'failed') {
                console.log("Pagamento falhou para a transação:", transaction_id); // Log de falha
                alert('Pagamento falhou. Tente novamente.');
                clearInterval(paymentCheckInterval); // Parar a verificação após a falha
            } else {
                console.log("Status da transação ainda não confirmado:", transaction_id); // Log de status pendente
            }
        },
        error: function(xhr, status, error) {
            console.error("Erro na verificação do status de pagamento:", error); // Log de erro
        }
    });
}
