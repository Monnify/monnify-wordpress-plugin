const { registerPaymentMethod } = window.wc.wcBlocksRegistry;
const { createElement } = window.wp.element;
const { __ } = window.wp.i18n;

const logoUrl = window.wc_monnify_params?.logo_url;

const MonnifyOptions = {
    name: 'monnify',
    label: createElement(
        'div',
        { 
            style: { 
                display: 'flex',
                alignItems: 'center',
                gap: '8px'
            } 
        },
        [
            createElement(
                'img',
                {
                    src: logoUrl,
                    alt: __('Monnify', 'monnify-official'),
                    style: {
                        width: 'auto',
                        height: '20px',
                        maxHeight: '20px'
                    }
                }
            ),
            createElement(
                'span',
                null,
                __('Monnify', 'monnify-official')
            )
        ]
    ),
    content: createElement(
        'div',
        { className: 'monnify-payment-method-content' },
        __('Pay securely with Monnify', 'monnify-official')
    ),
    edit: createElement(
        'div',
        null,
        __('Monnify Payment Method', 'monnify-official')
    ),
    canMakePayment: () => true,
    ariaLabel: __('Monnify payment method', 'monnify-official'),
    supports: {
        features: window.wc_monnify_params?.supports || ['products'],
    },
};

registerPaymentMethod(MonnifyOptions);
  