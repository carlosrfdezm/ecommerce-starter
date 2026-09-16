// =====================================================
// UPLOADER DE IMÁGENES CON COMPRESIÓN EN EL NAVEGADOR
// =====================================================
// Comprime la imagen antes de subirla usando Canvas API
// No requiere GD ni extensiones PHP en el servidor

class ImageUploader {
    constructor(options) {
        this.container = document.querySelector(options.container);
        this.inputHidden = document.querySelector(options.input);
        this.tipo = options.tipo || 'producto';
        this.anchoMax = options.anchoMax || 1600;
        this.calidad = options.calidad || 0.82;
        this.endpoint = options.endpoint || '../api/admin/upload.php';
        this.urlInicial = options.urlInicial || '';

        if (!this.container || !this.inputHidden) {
            console.warn('ImageUploader: contenedor o input no encontrado');
            return;
        }

        this.render();
        this.bindEvents();

        if (this.urlInicial) {
            this.mostrarPreview(this.urlInicial);
        }
    }

    render() {
        this.container.innerHTML = `
            <div class="uploader-dropzone" tabindex="0">
                <input type="file" class="uploader-file-input" accept="image/jpeg,image/png,image/webp,image/gif" hidden>
                <div class="uploader-empty">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p><strong>Arrastra una imagen aquí</strong> o haz clic para seleccionar</p>
                    <small>JPG, PNG, WEBP o GIF · máx 5 MB (se comprime automáticamente)</small>
                </div>
                <div class="uploader-preview" style="display:none;">
                    <img class="uploader-preview-img" src="" alt="Preview">
                    <div class="uploader-preview-info">
                        <span class="uploader-info-nombre"></span>
                        <span class="uploader-info-peso"></span>
                        <span class="uploader-info-dim"></span>
                    </div>
                    <div class="uploader-actions">
                        <button type="button" class="uploader-btn uploader-btn-change">
                            <i class="fas fa-exchange-alt"></i> Cambiar
                        </button>
                        <button type="button" class="uploader-btn uploader-btn-remove">
                            <i class="fas fa-trash"></i> Quitar
                        </button>
                    </div>
                </div>
                <div class="uploader-progress" style="display:none;">
                    <div class="uploader-progress-bar">
                        <div class="uploader-progress-fill"></div>
                    </div>
                    <p class="uploader-progress-text">Comprimiendo y subiendo...</p>
                </div>
            </div>
        `;

        this.dropzone = this.container.querySelector('.uploader-dropzone');
        this.fileInput = this.container.querySelector('.uploader-file-input');
        this.empty = this.container.querySelector('.uploader-empty');
        this.preview = this.container.querySelector('.uploader-preview');
        this.previewImg = this.container.querySelector('.uploader-preview-img');
        this.infoNombre = this.container.querySelector('.uploader-info-nombre');
        this.infoPeso = this.container.querySelector('.uploader-info-peso');
        this.infoDim = this.container.querySelector('.uploader-info-dim');
        this.progress = this.container.querySelector('.uploader-progress');
        this.progressFill = this.container.querySelector('.uploader-progress-fill');
        this.btnChange = this.container.querySelector('.uploader-btn-change');
        this.btnRemove = this.container.querySelector('.uploader-btn-remove');
    }

    bindEvents() {
        // Click en la dropzone → abrir selector
        this.dropzone.addEventListener('click', (e) => {
            if (e.target.closest('.uploader-btn')) return;
            if (this.preview.style.display !== 'none') return;
            this.fileInput.click();
        });

        // Teclado accesible
        this.dropzone.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.fileInput.click();
            }
        });

        // Seleccionar archivo
        this.fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                this.procesarArchivo(e.target.files[0]);
            }
            e.target.value = '';
        });

        // Drag & drop
        ['dragenter', 'dragover'].forEach(ev => {
            this.dropzone.addEventListener(ev, (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.dropzone.classList.add('dragover');
            });
        });

        ['dragleave', 'drop'].forEach(ev => {
            this.dropzone.addEventListener(ev, (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.dropzone.classList.remove('dragover');
            });
        });

        this.dropzone.addEventListener('drop', (e) => {
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                this.procesarArchivo(files[0]);
            }
        });

        // Botones
        this.btnChange.addEventListener('click', (e) => {
            e.stopPropagation();
            this.fileInput.click();
        });

        this.btnRemove.addEventListener('click', (e) => {
            e.stopPropagation();
            if (!confirm('¿Quitar la imagen?')) return;
            this.limpiar();
        });
    }

    async procesarArchivo(file) {
        // Validaciones previas
        if (!file.type.startsWith('image/')) {
            alert('❌ El archivo debe ser una imagen');
            return;
        }

        if (file.size > 5 * 1024 * 1024) {
            alert('❌ La imagen supera los 5 MB');
            return;
        }

        this.mostrarProgreso(10);

        try {
            // Comprimir
            const blob = await this.comprimirImagen(file);

            this.mostrarProgreso(50);

            // Subir
            const resultado = await this.subir(blob, file.name);

            this.mostrarProgreso(100);

            if (resultado.success) {
                // Guardar URL en el input hidden
                this.inputHidden.value = resultado.url;
                this.mostrarPreview(resultado.url, {
                    nombre: resultado.nombre_archivo,
                    peso: resultado.peso,
                    ancho: blob.anchoOriginal || 0,
                    alto: blob.altoOriginal || 0
                });
            } else {
                alert('❌ ' + (resultado.error || 'Error al subir la imagen'));
                this.ocultarProgreso();
            }
        } catch (err) {
            console.error('Error procesando imagen:', err);
            alert('❌ Error al procesar la imagen: ' + err.message);
            this.ocultarProgreso();
        }
    }

    comprimirImagen(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();

            reader.onload = (e) => {
                const img = new Image();

                img.onload = () => {
                    let ancho = img.width;
                    let alto = img.height;

                    // Redimensionar si supera el ancho máximo
                    if (ancho > this.anchoMax) {
                        alto = Math.round((this.anchoMax / ancho) * alto);
                        ancho = this.anchoMax;
                    }

                    const canvas = document.createElement('canvas');
                    canvas.width = ancho;
                    canvas.height = alto;

                    const ctx = canvas.getContext('2d');
                    // Fondo blanco (por si la imagen tiene transparencia y se convierte a JPG)
                    ctx.fillStyle = '#ffffff';
                    ctx.fillRect(0, 0, ancho, alto);
                    ctx.drawImage(img, 0, 0, ancho, alto);

                    // Intentar WebP primero (mejor compresión), fallback a JPG
                    const tipoSalida = this.soportaWebP() ? 'image/webp' : 'image/jpeg';
                    const extSalida = tipoSalida === 'image/webp' ? 'webp' : 'jpg';

                    canvas.toBlob(
                        (blob) => {
                            if (!blob) return reject(new Error('No se pudo comprimir'));
                            blob.anchoOriginal = ancho;
                            blob.altoOriginal = alto;
                            blob.extSalida = extSalida;
                            blob.nombreOriginal = file.name;
                            resolve(blob);
                        },
                        tipoSalida,
                        this.calidad
                    );
                };

                img.onerror = () => reject(new Error('La imagen está corrupta o no es válida'));
                img.src = e.target.result;
            };

            reader.onerror = () => reject(new Error('Error al leer el archivo'));
            reader.readAsDataURL(file);
        });
    }

    soportaWebP() {
        const canvas = document.createElement('canvas');
        canvas.width = 1;
        canvas.height = 1;
        return canvas.toDataURL('image/webp').indexOf('data:image/webp') === 0;
    }

    subir(blob, nombreOriginal) {
        return new Promise((resolve, reject) => {
            const formData = new FormData();
            const ext = blob.extSalida || 'jpg';
            const nombreLimpio = nombreOriginal.replace(/\.[^/.]+$/, '') + '.' + ext;
            formData.append('archivo', blob, nombreLimpio);
            formData.append('tipo', this.tipo);

            const xhr = new XMLHttpRequest();

            xhr.upload.onprogress = (e) => {
                if (e.lengthComputable) {
                    const pct = 50 + (e.loaded / e.total) * 50;
                    this.mostrarProgreso(pct);
                }
            };

            xhr.onload = () => {
                try {
                    const resp = JSON.parse(xhr.responseText);
                    resolve(resp);
                } catch (err) {
                    reject(new Error('Respuesta inválida del servidor'));
                }
            };

            xhr.onerror = () => reject(new Error('Error de red al subir la imagen'));
            xhr.open('POST', this.endpoint);
            xhr.send(formData);
        });
    }

    mostrarPreview(url, info) {
        let src = url;
        if (url && !url.startsWith('http') && !url.startsWith('/')) {
            src = '../' + url;
        }
        this.previewImg.src = src;
        this.empty.style.display = 'none';
        this.preview.style.display = 'block';
        this.ocultarProgreso();

        if (info) {
            this.infoNombre.textContent = info.nombre || '';
            this.infoPeso.textContent = this.formatearPeso(info.peso || 0);
            this.infoDim.textContent = (info.ancho && info.alto) ? `${info.ancho}×${info.alto}px` : '';
        } else {
            this.infoNombre.textContent = '';
            this.infoPeso.textContent = '';
            this.infoDim.textContent = '';
        }
    }

    limpiar() {
        this.inputHidden.value = '';
        this.previewImg.src = '';
        this.preview.style.display = 'none';
        this.empty.style.display = 'block';
        this.ocultarProgreso();
    }

    mostrarProgreso(pct) {
        this.empty.style.display = 'none';
        this.preview.style.display = 'none';
        this.progress.style.display = 'block';
        this.progressFill.style.width = pct + '%';
    }

    ocultarProgreso() {
        this.progress.style.display = 'none';
        this.progressFill.style.width = '0%';
    }

    formatearPeso(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / 1024 / 1024).toFixed(2) + ' MB';
    }
}

window.ImageUploader = ImageUploader;