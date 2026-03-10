
<?php $__env->startSection('title'); ?> <?php echo app('translator')->get('translation.faqs'); ?> <?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <?php $__env->startComponent('components.breadcrumb'); ?>
        <?php $__env->slot('li_1'); ?> Pages <?php $__env->endSlot(); ?>
        <?php $__env->slot('title'); ?> 
            Preguntas Frecuentes - Sistema Electoral 
        <?php $__env->endSlot(); ?>
    <?php echo $__env->renderComponent(); ?>

    <div class="row mt-2">
        <div class="col-lg-12">
            <div class="card rounded-0 bg-success-subtle mx-n4 mt-n4 border-top">
                <div class="px-4">
                    <div class="row">
                        <div class="col-xxl-5 align-self-center">
                            <div class="py-4">
                                <h4 class="display-6 coming-soon-text">Preguntas Frecuentes</h4>
                                <p class="text-success fs-15 mt-3">Si no encuentra respuesta a su pregunta en nuestras FAQ, puede contactarnos. ¡Le responderemos a la brevedad!</p>
                                <div class="hstack flex-wrap gap-2">
                                    <button type="button" class="btn btn-primary btn-label rounded-pill"><i class="ri-mail-line label-icon align-middle rounded-pill fs-16 me-2"></i> Contáctenos</button>
                                    <button type="button" class="btn btn-info btn-label rounded-pill"><i class="ri-phone-line label-icon align-middle rounded-pill fs-16 me-2"></i> Soporte Técnico</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-xxl-3 ms-auto">
                            <div class="mb-n5 pb-1 faq-img d-none d-xxl-block">
                                <img src="<?php echo e(URL::asset('build/images/faq-img.png')); ?>" alt="" class="img-fluid">
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end card body -->
            </div>
            <!-- end card -->

            <div class="row justify-content-evenly mb-4">
                <div class="col-lg-4">
                    <div class="mt-3">
                        <div class="d-flex align-items-center mb-2">
                            <div class="flex-shrink-0 me-1">
                                <i class="ri-question-line fs-24 align-middle text-success me-1"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="fs-16 mb-0 fw-semibold">Preguntas Generales</h5>
                            </div>
                        </div>

                        <div class="accordion accordion-border-box" id="genques-accordion">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="genques-headingOne">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#genques-collapseOne" aria-expanded="true" aria-controls="genques-collapseOne">
                                        ¿Qué es el Sistema de Conteo de Votos?
                                    </button>
                                </h2>
                                <div id="genques-collapseOne" class="accordion-collapse collapse show" aria-labelledby="genques-headingOne" data-bs-parent="#genques-accordion">
                                    <div class="accordion-body">
                                        Es una plataforma digital diseñada para el registro, conteo y seguimiento de votos en procesos electorales. El sistema organiza la información por departamentos, provincias, municipios, localidades, distritos, zonas, instituciones y mesas de votación, garantizando transparencia y precisión en los resultados.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="genques-headingTwo">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#genques-collapseTwo" aria-expanded="false" aria-controls="genques-collapseTwo">
                                        ¿Cómo se organiza la información electoral?
                                    </button>
                                </h2>
                                <div id="genques-collapseTwo" class="accordion-collapse collapse" aria-labelledby="genques-headingTwo" data-bs-parent="#genques-accordion">
                                    <div class="accordion-body">
                                        La información se estructura jerárquicamente: <strong>Departamentos → Provincias → Municipios → Localidades → Distritos → Zones → Instituciones → Mesas de Votación</strong>. Cada nivel contiene datos específicos como coordenadas geográficas, número de ciudadanos registrados y estados de las actas.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="genques-headingThree">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#genques-collapseThree" aria-expanded="false" aria-controls="genques-collapseThree">
                                        ¿Qué tipos de elecciones soporta el sistema?
                                    </button>
                                </h2>
                                <div id="genques-collapseThree" class="accordion-collapse collapse" aria-labelledby="genques-headingThree" data-bs-parent="#genques-accordion">
                                    <div class="accordion-body">
                                        El sistema soporta múltiples tipos de elecciones: <strong>Presidente, Senador, Diputado, Alcalde y Concejal</strong>. Cada tipo electoral tiene sus propios candidatos y configuración específica, permitiendo manejar procesos electorales simultáneos de diferentes categorías.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="genques-headingFour">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#genques-collapseFour" aria-expanded="false" aria-controls="genques-collapseFour">
                                        ¿Qué significan los estados de las mesas de votación?
                                    </button>
                                </h2>
                                <div id="genques-collapseFour" class="accordion-collapse collapse" aria-labelledby="genques-headingFour" data-bs-parent="#genques-accordion">
                                    <div class="accordion-body">
                                        Las mesas de votación pueden tener tres estados: <strong>Activa</strong> (en proceso), <strong>Cerrada</strong> (proceso completado) y <strong>Pendiente</strong> (aún no iniciada). Además, se registran actas computadas, anuladas y habilitadas para cada mesa e institución.
                                    </div>
                                </div>
                            </div>
                        </div><!--end accordion-->
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="mt-3">
                        <div class="d-flex align-items-center mb-2">
                            <div class="flex-shrink-0 me-1">
                                <i class="ri-user-settings-line fs-24 align-middle text-success me-1"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="fs-16 mb-0 fw-semibold">Gestión del Sistema</h5>
                            </div>
                        </div>

                        <div class="accordion accordion-border-box" id="manageaccount-accordion">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="manageaccount-headingOne">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#manageaccount-collapseOne" aria-expanded="false" aria-controls="manageaccount-collapseOne">
                                        ¿Cómo se registran los votos?
                                    </button>
                                </h2>
                                <div id="manageaccount-collapseOne" class="accordion-collapse collapse" aria-labelledby="manageaccount-headingOne" data-bs-parent="#manageaccount-accordion">
                                    <div class="accordion-body">
                                        Los votos se registran por mesa de votación, candidato y tipo de elección. Cada registro incluye la cantidad de votos, porcentaje calculado, y es verificado por un usuario autorizado. El sistema evita duplicados mediante restricciones únicas en la base de datos.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="manageaccount-headingTwo">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#manageaccount-collapseTwo" aria-expanded="true" aria-controls="manageaccount-collapseTwo">
                                        ¿Qué información contiene cada candidato?
                                    </button>
                                </h2>
                                <div id="manageaccount-collapseTwo" class="accordion-collapse collapse show" aria-labelledby="manageaccount-headingTwo" data-bs-parent="#manageaccount-accordion">
                                    <div class="accordion-body">
                                        Cada candidato registra: nombre, partido político, nombre completo del partido, logo del partido, foto del candidato, color representativo y tipo de elección. Además, se distinguen entre candidatos regulares, votos en blanco y votos nulos.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="manageaccount-headingThree">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#manageaccount-collapseThree" aria-expanded="false" aria-controls="manageaccount-collapseThree">
                                        ¿Cómo se calculan los porcentajes?
                                    </button>
                                </h2>
                                <div id="manageaccount-collapseThree" class="accordion-collapse collapse" aria-labelledby="manageaccount-headingThree" data-bs-parent="#manageaccount-accordion">
                                    <div class="accordion-body">
                                        Los porcentajes se calculan automáticamente en base al total de votos válidos por mesa de votación y tipo de elección. El sistema actualiza estos porcentajes en tiempo real a medida que se ingresan nuevos datos, proporcionando resultados precisos y actualizados.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="manageaccount-headingFour">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#manageaccount-collapseFour" aria-expanded="false" aria-controls="manageaccount-collapseFour">
                                        ¿Qué es el Dashboard del sistema?
                                    </button>
                                </h2>
                                <div id="manageaccount-collapseFour" class="accordion-collapse collapse" aria-labelledby="manageaccount-headingFour" data-bs-parent="#manageaccount-accordion">
                                    <div class="accordion-body">
                                        El Dashboard es el panel principal que muestra los resultados electorales. Puede configurarse como público (accesible para todos) o privado (solo usuarios autorizados). Muestra estadísticas en tiempo real, progreso del conteo y resultados por diferentes categorías geográficas.
                                    </div>
                                </div>
                            </div>
                        </div><!--end accordion-->
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="mt-3">
                        <div class="d-flex align-items-center mb-2">
                            <div class="flex-shrink-0 me-1">
                                <i class="ri-shield-keyhole-line fs-24 align-middle text-success me-1"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="fs-16 mb-0 fw-semibold">Seguridad y Acceso</h5>
                            </div>
                        </div>

                        <div class="accordion accordion-border-box" id="privacy-accordion">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="privacy-headingOne">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#privacy-collapseOne" aria-expanded="true" aria-controls="privacy-collapseOne">
                                        ¿Quién puede acceder al sistema?
                                    </button>
                                </h2>
                                <div id="privacy-collapseOne" class="accordion-collapse collapse show" aria-labelledby="privacy-headingOne" data-bs-parent="#privacy-accordion">
                                    <div class="accordion-body">
                                        El acceso está restringido a usuarios autorizados con credenciales específicas. Los administradores pueden gestionar permisos y roles. El dashboard puede hacerse público para visualización de resultados o mantenerse privado para solo personal autorizado, dependiendo de la configuración del proceso electoral.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="privacy-headingTwo">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#privacy-collapseTwo" aria-expanded="false" aria-controls="privacy-collapseTwo">
                                        ¿Cómo se garantiza la integridad de los datos?
                                    </button>
                                </h2>
                                <div id="privacy-collapseTwo" class="accordion-collapse collapse" aria-labelledby="privacy-headingTwo" data-bs-parent="#privacy-accordion">
                                    <div class="accordion-body">
                                        El sistema implementa múltiples medidas: validaciones de entrada, restricciones únicas para evitar duplicados, registro de usuario que realiza cada modificación, timestamps de verificación, y cálculos automáticos que previenen inconsistencias. Todos los cambios quedan auditados en el sistema.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="privacy-headingThree">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#privacy-collapseThree" aria-expanded="false" aria-controls="privacy-collapseThree">
                                        ¿Qué hacer si encuentro un error en los datos?
                                    </button>
                                </h2>
                                <div id="privacy-collapseThree" class="accordion-collapse collapse" aria-labelledby="privacy-headingThree" data-bs-parent="#privacy-accordion">
                                    <div class="accordion-body">
                                        Contacte inmediatamente al administrador del sistema o al soporte técnico. Proporcione detalles específicos: mesa de votación, candidato, tipo de elección y la discrepancia encontrada. Solo usuarios autorizados pueden corregir datos, previa validación y registro de la modificación.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="privacy-headingFour">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#privacy-collapseFour" aria-expanded="false" aria-controls="privacy-collapseFour">
                                        ¿El sistema guarda histórico de cambios?
                                    </button>
                                </h2>
                                <div id="privacy-collapseFour" class="accordion-collapse collapse" aria-labelledby="privacy-headingFour" data-bs-parent="#privacy-accordion">
                                    <div class="accordion-body">
                                        Sí, el sistema registra todas las modificaciones con timestamps y el usuario responsable. Esto crea un trail de auditoría completo que permite rastrear cualquier cambio realizado en los datos electorales, garantizando transparencia y permitiendo recuperaciones en caso de errores.
                                    </div>
                                </div>
                            </div>
                        </div><!--end accordion-->
                    </div>
                </div>
            </div>

            <!-- Additional Section for Technical Questions -->
            <div class="row mt-4">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-1">
                                    <i class="ri-code-s-slash-line fs-24 align-middle text-success me-1"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="fs-16 mb-0 fw-semibold">Aspectos Técnicos</h5>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="accordion accordion-border-box" id="technical-accordion">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="technical-headingOne">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#technical-collapseOne" aria-expanded="false" aria-controls="technical-collapseOne">
                                            ¿Cómo se manejan las coordenadas geográficas?
                                        </button>
                                    </h2>
                                    <div id="technical-collapseOne" class="accordion-collapse collapse" aria-labelledby="technical-headingOne" data-bs-parent="#technical-accordion">
                                        <div class="accordion-body">
                                            Las coordenadas (latitud y longitud) se almacenan con precisión de 7 decimales para ubicación precisa de departamentos, provincias, municipios y localidades. Esto permite mapping geográfico y análisis territorial de los resultados electorales.
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="technical-headingTwo">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#technical-collapseTwo" aria-expanded="false" aria-controls="technical-collapseTwo">
                                            ¿Qué significa "ciudadanos registrados" vs "votos computados"?
                                        </button>
                                    </h2>
                                    <div id="technical-collapseTwo" class="accordion-collapse collapse" aria-labelledby="technical-headingTwo" data-bs-parent="#technical-accordion">
                                        <div class="accordion-body">
                                            "Ciudadanos registrados" representa el padrón electoral oficial para cada mesa/institución. "Votos computados" son los votos válidos efectivamente contabilizados. La diferencia puede indicar abstencionismo o votos anulados, proporcionando métricas importantes para el análisis electoral.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>
    <script src="<?php echo e(URL::asset('build/js/app.js')); ?>"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\_Mine\sistema_electoral\resources\views/pages-faqs.blade.php ENDPATH**/ ?>