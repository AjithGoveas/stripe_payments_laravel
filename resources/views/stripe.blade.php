<!DOCTYPE html>
<html>
<head>
    <title>Laravel Stripe Payment - Multiple Methods</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <script src="https://js.stripe.com/v3/"></script>
    <style>
        #payment-element {
            margin-top: 16px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row mt-5">
        <div class="col-md-6 offset-md-3">
            <div class="card">
                <h4 class="card-header">Stripe Payment (Multiple Methods)</h4>
                <div class="card-body">

                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <form id="payment-form">
                        @csrf
                        <div class="mb-3">
                            <label for="name">Name:</label>
                            <input id="name" name="name" class="form-control" required>
                        </div>

                        <div id="payment-element"></div>
                        <div id="error-message" class="text-danger mt-2"></div>

                        <button id="submit" class="btn btn-primary w-100 mt-3">Pay</button>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const stripe = Stripe("{{ env('STRIPE_KEY') }}");

    fetch("/create-payment-intent", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({
            amount: 1000 // $10
        })
    })
    .then(response => response.json())
    .then(({ clientSecret }) => {
        const elements = stripe.elements({ clientSecret });
        const paymentElement = elements.create("payment");
        paymentElement.mount("#payment-element");

        const form = document.getElementById("payment-form");

        form.addEventListener("submit", async (event) => {
            event.preventDefault();

            const { error } = await stripe.confirmPayment({
                elements,
                confirmParams: {
                    return_url: "{{ route('stripe.success') }}",
                },
            });

            if (error) {
                const messageContainer = document.getElementById("error-message");
                messageContainer.textContent = error.message;
            }
        });
    });
</script>

</body>
</html>
