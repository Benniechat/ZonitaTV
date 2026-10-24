@once
<script src="https://upload-widget.cloudinary.com/global/all.js" type="text/javascript"></script>
<script>
window.CloudinaryConfig = {
    cloudName: "{{ config('services.cloudinary.cloud_name', 'jcjugta9') }}",
    apiKey: "{{ config('services.cloudinary.api_key', '269289579262165') }}",
    uploadPreset: "{{ config('services.cloudinary.upload_preset', 'zonita_uploads') }}",
    signatureUrl: "{{ route('admin.media.signature') }}"
};

/**
 * Open Cloudinary Upload Widget modal with full customization
 *
 * @param {Object} options
 * @param {string} [options.folder='zonita']
 * @param {boolean} [options.cropping=false]
 * @param {number} [options.croppingAspectRatio=null]
 * @param {string} [options.targetInputId=null] - input element to receive the secure_url
 * @param {string} [options.previewImageId=null] - img element to update preview src
 * @param {Function} [options.onSuccess=null] - callback function (result) => {}
 */
window.openCloudinaryWidget = function (options = {}) {
    if (!window.cloudinary) {
        alert('Cloudinary Upload Widget SDK is still loading or failed to connect.');
        return;
    }

    const folder = options.folder || 'zonita/media';
    const cropping = options.cropping ?? false;
    const croppingAspectRatio = options.croppingAspectRatio ?? null;

    // Fetch signature or open widget
    fetch(`${window.CloudinaryConfig.signatureUrl}?folder=${encodeURIComponent(folder)}`)
        .then(res => res.json())
        .then(sigData => {
            const widgetOptions = {
                cloudName: sigData.cloud_name || window.CloudinaryConfig.cloudName,
                apiKey: sigData.api_key || window.CloudinaryConfig.apiKey,
                folder: folder,
                sources: ['local', 'url', 'camera'],
                multiple: false,
                defaultSource: 'local',
                clientAllowedFormats: ['png', 'jpg', 'jpeg', 'webp', 'svg', 'gif', 'mp4'],
                maxFileSize: 25000000, // 25MB
                styles: {
                    palette: {
                        window: '#0E1117',
                        windowBorder: '#1F2937',
                        tabIcon: '#F87171',
                        menuIcons: '#9CA3AF',
                        textDark: '#111827',
                        textLight: '#F9FAFB',
                        link: '#EF4444',
                        action: '#DC2626',
                        inactiveTabIcon: '#6B7280',
                        error: '#EF4444',
                        inProgress: '#3B82F6',
                        complete: '#10B981',
                        sourceBg: '#111827'
                    }
                }
            };

            if (sigData.is_signed && sigData.signature) {
                widgetOptions.uploadSignatureTimestamp = sigData.timestamp;
                widgetOptions.uploadSignature = sigData.signature;
            } else {
                widgetOptions.uploadPreset = sigData.upload_preset || window.CloudinaryConfig.uploadPreset;
            }

            if (cropping) {
                widgetOptions.cropping = true;
                widgetOptions.showSkipCropButton = false;
                if (croppingAspectRatio) {
                    widgetOptions.croppingAspectRatio = croppingAspectRatio;
                }
            }

            const myWidget = window.cloudinary.createUploadWidget(
                widgetOptions,
                (error, result) => {
                    if (!error && result && result.event === "success") {
                        const info = result.info;
                        const secureUrl = info.secure_url;

                        if (options.targetInputId) {
                            const input = document.getElementById(options.targetInputId);
                            if (input) {
                                input.value = secureUrl;
                                input.dispatchEvent(new Event('input', { bubbles: true }));
                                input.dispatchEvent(new Event('change', { bubbles: true }));
                            }
                        }

                        if (options.previewImageId) {
                            const img = document.getElementById(options.previewImageId);
                            if (img) {
                                img.src = secureUrl;
                                img.classList.remove('hidden');
                            }
                        }

                        if (typeof options.onSuccess === 'function') {
                            options.onSuccess(info);
                        }
                    } else if (error) {
                        console.error('Cloudinary Upload Widget Error:', error);
                    }
                }
            );

            myWidget.open();
        })
        .catch(err => {
            console.error('Failed to initialize Cloudinary widget:', err);
            alert('Could not initialize Cloudinary upload. Please check your network or credentials.');
        });
};
</script>
@endonce
