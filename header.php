<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="profile" href="http://gmpg.org/xfn/11">
    <?php wp_head(); ?>
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/style.css">
</head>
<body <?php body_class(); ?>>
    <?php
    // Verificar si estamos en la página principal o en la página de registro
    if (!is_front_page() && !is_page('register')) {
        ?>
        <div class="menu-vertical">
            <!-- Imagen Instagram -->
            <!-- Imagen Instagram -->
            <div class="menu-item instagram-item">
                <a href="<?php echo get_permalink(get_page_by_path('inicio')); ?>" target="_self">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/instagram.png" alt="Instagram" class="instagram-icon">
                </a>
            </div>
            <?php
            wp_nav_menu(array(
                'theme_location' => 'primary',
                'container' => false,
                'items_wrap' => '<ul>%3$s</ul>',
            ));
            ?>
            <div class="search-container">
                <!-- Checkbox para activar el panel -->
<!-- Checkbox para activar el panel -->
<input type="checkbox" id="search-toggle" class="search-toggle-checkbox">
<label for="search-toggle" class="search-toggle-button">
    <span class="search-icon-container">
        <img src="<?php echo get_template_directory_uri(); ?>/assets/lupa.png" alt="Buscar" class="search-icon">
    </span>
    <span class="search-text">Busqueda</span>
</label>


                
                <div id="search-panel" class="search-panel">
                    <form id="search-form" action="#" method="get">
                        <input type="text" id="search-query" name="query" placeholder="Buscar usuarios...">
                        <button type="submit">Buscar</button>
                    </form>
                    <div id="search-results"></div>
                </div>
            </div>
            <div class="menu-item">
                <button id="create-post-button-header">Crear</button>
            </div>
        </div>
        <?php
    }
    ?>
    <div id="content">
        <!-- Modal -->
        <div id="create-post-modal-header" class="modal">
            <div class="modal-content">
                <span id="close-modal-header" class="close">&times;</span>
                <h2>Crear una nueva publicación</h2>
                <img id="modal-image-header" src="" alt="Imagen" class="modal-image" style="display: none;" />
                <div class="form-container">
                    <form id="create-post-form-header" method="post" enctype="multipart/form-data">
                        <input type="file" id="file-input-header" name="media_file" accept="image/*,video/*" />
                        <input type="text" id="post-title-header" name="title" class="input-field" placeholder="Título" />
                        <textarea id="post-content-header" name="content" class="input-field" placeholder="Contenido"></textarea>
                        <button type="submit" id="share-button-header">Compartir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        document.addEventListener("DOMContentLoaded", function() {
            var modalHeader = document.getElementById("create-post-modal-header");
            var openModalButtonHeader = document.getElementById("create-post-button-header");
            var closeModalButtonHeader = document.getElementById("close-modal-header");
            var fileInputHeader = document.getElementById("file-input-header");
            var modalImageHeader = document.getElementById("modal-image-header");

            // Mostrar el modal
            openModalButtonHeader.addEventListener("click", function() {
                modalHeader.style.display = "block";
            });

            // Cerrar el modal
            closeModalButtonHeader.addEventListener("click", function() {
                modalHeader.style.display = "none";
                modalImageHeader.style.display = "none"; 
                fileInputHeader.value = ""; 
            });

            window.onclick = function(event) {
                if (event.target == modalHeader) {
                    modalHeader.style.display = "none";
                    modalImageHeader.style.display = "none";
                    fileInputHeader.value = "";
                }
            };

            // Manejar la selección de archivos
            fileInputHeader.addEventListener("change", function(event) {
                event.preventDefault(); 

                var file = fileInputHeader.files[0];
                if (file) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        modalImageHeader.src = e.target.result;
                        modalImageHeader.style.display = "block";
                    }
                    reader.readAsDataURL(file);
                }
            });
        });
    </script>
<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['media_file'])) {
    $file = $_FILES['media_file'];
    $title = sanitize_text_field($_POST['title']);
    $content = sanitize_textarea_field($_POST['content']);
    $current_user = wp_get_current_user(); 
    $userEmail = $current_user->user_email; 

    // Subir el archivo a la biblioteca de medios
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    $upload = wp_handle_upload($file, array('test_form' => false));

    if (!isset($upload['error']) && isset($upload['url'])) {
        $file_url = $upload['url'];

        // Enviar los datos a Airtable
        $response = wp_remote_post('https://api.airtable.com/v0/appzmB3zBmwWkhnkn/Posts', array(
            'method'    => 'POST',
            'headers'   => array(
                'Authorization' => 'Bearer patv59bjnbEGUFZG8.cd0546b6e89b9368307894b52c97ef81268d5253071ed72b4d94d955b441b576',
                'Content-Type'  => 'application/json',
            ),
            'body'      => json_encode(array(
                'fields' => array(
                    'Titulo' => $title,
                    'Contenido' => $content,
                    'Imagen/Video' => $file_url,
                    'Email de Usuario' => $userEmail 
                ),
            )),
        ));

        if (!is_wp_error($response)) {
            echo '<script type="text/javascript">
                document.addEventListener("DOMContentLoaded", function() {
                    var successModal = document.getElementById("success-modal");
                    var closeSuccessModalButton = document.getElementById("close-success-modal");
                    successModal.style.display = "block";

                    closeSuccessModalButton.addEventListener("click", function() {
                        successModal.style.display = "none";
                    });

                    window.onclick = function(event) {
                        if (event.target == successModal) {
                            successModal.style.display = "none";
                        }
                    };
                });
            </script>';
            echo '<div id="success-modal" class="success-modal">
                <div class="success-modal-content">
                    <span id="close-success-modal" class="success-close">&times;</span>
                    <h2>¡Publicación creada exitosamente!</h2>
                    <div class="success-modal-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="success-icon">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>
            </div>';
        } else {
            echo 'Error al enviar los datos a Airtable.';
        }
    } else {
        echo 'Error al subir el archivo.';
    }
}
?>
    <?php wp_footer(); ?>
</body>
</html>