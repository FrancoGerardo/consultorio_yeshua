<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import { useForm } from '@inertiajs/vue3';

const pagina = usePage();

defineProps({
    canLogin: {
        type: Boolean,
        default: true,
    },
    canRegister: {
        type: Boolean,
        default: true,
    },
});

const formularioContacto = useForm({
    nombre: '',
    email: '',
    telefono: '',
    mensaje: '',
});

const enviarContacto = () => {
    formularioContacto.post(route('contacto.enviar'), {
        preserveScroll: true,
        onSuccess: () => {
            formularioContacto.reset();
        },
        onError: () => {
            // Los errores se mostrarán automáticamente con InputError
        },
    });
};

const mostrarMenu = ref(false);

// Scroll suave para los enlaces del menú
onMounted(() => {
    // Manejar clicks en enlaces de anclaje
    const enlacesAncla = document.querySelectorAll('a[href^="#"]');
    enlacesAncla.forEach(enlace => {
        enlace.addEventListener('click', (e) => {
            const href = enlace.getAttribute('href');
            if (href !== '#' && href !== '') {
                e.preventDefault();
                const destino = document.querySelector(href);
                if (destino) {
                    destino.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    mostrarMenu.value = false; // Cerrar menú móvil si está abierto
                }
            }
        });
    });
    
    // Mostrar mensaje de éxito si existe
    if (pagina.props.flash?.success) {
        alert(pagina.props.flash.success);
    }
});
</script>

<template>
    <Head title="Inicio - Consultorio Medico Yeshua" />
    
    <div class="min-h-screen bg-gradient-to-b from-blue-50 to-white">
        <!-- Navegación -->
        <nav class="bg-white shadow-md sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center">
                        <Link :href="route('welcome')" class="flex items-center space-x-2">
                            <ApplicationMark class="h-10 w-10" />
                            <span class="text-xl font-bold text-indigo-600">Consultorio Medico Yeshua</span>
                        </Link>
                    </div>
                    
                    <!-- Menú Desktop -->
                    <div class="hidden md:flex items-center space-x-8">
                        <a href="#inicio" class="text-gray-700 hover:text-indigo-600 transition">Inicio</a>
                        <a href="#caracteristicas" class="text-gray-700 hover:text-indigo-600 transition">Características</a>
                        <a href="#ventajas" class="text-gray-700 hover:text-indigo-600 transition">Ventajas</a>
                        <a href="#testimonios" class="text-gray-700 hover:text-indigo-600 transition">Testimonios</a>
                        <a href="#noticias" class="text-gray-700 hover:text-indigo-600 transition">Noticias</a>
                        <a href="#contacto" class="text-gray-700 hover:text-indigo-600 transition">Contacto</a>
                        
                        <template v-if="canLogin">
                            <Link
                                v-if="$page.props.auth?.user"
                                :href="route('dashboard')"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition"
                            >
                                Dashboard
                            </Link>
                            <template v-else>
                                <Link
                                    :href="route('login')"
                                    class="text-gray-700 hover:text-indigo-600 transition"
                                >
                                    Iniciar Sesión
                                </Link>
                                <Link
                                    v-if="canRegister"
                                    :href="route('register')"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition"
                                >
                                    Registrarse
                                </Link>
                            </template>
                        </template>
                    </div>
                    
                    <!-- Botón Menú Móvil -->
                    <button
                        @click="mostrarMenu = !mostrarMenu"
                        class="md:hidden text-gray-700 hover:text-indigo-600"
                    >
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
                
                <!-- Menú Móvil -->
                <div v-show="mostrarMenu" class="md:hidden py-4 space-y-2">
                    <a href="#inicio" class="block text-gray-700 hover:text-indigo-600">Inicio</a>
                    <a href="#caracteristicas" class="block text-gray-700 hover:text-indigo-600">Características</a>
                    <a href="#ventajas" class="block text-gray-700 hover:text-indigo-600">Ventajas</a>
                    <a href="#testimonios" class="block text-gray-700 hover:text-indigo-600">Testimonios</a>
                    <a href="#noticias" class="block text-gray-700 hover:text-indigo-600">Noticias</a>
                    <a href="#contacto" class="block text-gray-700 hover:text-indigo-600">Contacto</a>
                    <div class="pt-4 border-t">
                        <template v-if="canLogin">
                            <Link
                                v-if="$page.props.auth?.user"
                                :href="route('dashboard')"
                                class="block px-4 py-2 bg-indigo-600 text-white rounded-md text-center"
                            >
                                Dashboard
                            </Link>
                            <template v-else>
                                <Link
                                    :href="route('login')"
                                    class="block px-4 py-2 text-gray-700 hover:text-indigo-600 text-center"
                                >
                                    Iniciar Sesión
                                </Link>
                                <Link
                                    v-if="canRegister"
                                    :href="route('register')"
                                    class="block px-4 py-2 bg-indigo-600 text-white rounded-md text-center mt-2"
                                >
                                    Registrarse
                                </Link>
                            </template>
                        </template>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Sección Hero -->
        <section id="inicio" class="py-20 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto text-center">
                <h1 class="text-4xl md:text-6xl font-bold text-gray-900 mb-6">
                    Te brindamos todas las herramientas
                </h1>
                <h2 class="text-2xl md:text-4xl text-indigo-600 mb-8">
                    Para registrar la información de tus pacientes y administrar eficientemente tu clínica
                </h2>
                <p class="text-xl text-gray-600 mb-10 max-w-3xl mx-auto">
                    Sistema integral de gestión médica diseñado para profesionales de la salud que buscan optimizar su práctica clínica.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="px-8 py-4 bg-indigo-600 text-white rounded-lg text-lg font-semibold hover:bg-indigo-700 transition shadow-lg"
                    >
                        ¡Comienza Ahora!
                    </Link>
                    <Link
                        v-if="canLogin"
                        :href="route('login')"
                        class="px-8 py-4 bg-white text-indigo-600 border-2 border-indigo-600 rounded-lg text-lg font-semibold hover:bg-indigo-50 transition shadow-lg"
                    >
                        Iniciar Sesión
                    </Link>
                </div>
            </div>
        </section>

        <!-- Sección Características -->
        <section id="caracteristicas" class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl md:text-4xl font-bold text-center text-gray-900 mb-4">
                    Características
                </h2>
                <p class="text-center text-gray-600 mb-12 text-lg">
                    Te ofrecemos
                </p>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <!-- Característica 1 -->
                    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 p-6 rounded-lg shadow-md hover:shadow-xl transition">
                        <div class="text-4xl mb-4">📋</div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Gestión de Expedientes</h3>
                        <p class="text-gray-600">
                            Administra los expedientes electrónicos de tus pacientes de forma segura y organizada.
                        </p>
                    </div>
                    
                    <!-- Característica 2 -->
                    <div class="bg-gradient-to-br from-green-50 to-emerald-50 p-6 rounded-lg shadow-md hover:shadow-xl transition">
                        <div class="text-4xl mb-4">📅</div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Agenda Médica</h3>
                        <p class="text-gray-600">
                            Programa y gestiona tus citas médicas con facilidad, optimizando tu tiempo y recursos.
                        </p>
                    </div>
                    
                    <!-- Característica 3 -->
                    <div class="bg-gradient-to-br from-purple-50 to-pink-50 p-6 rounded-lg shadow-md hover:shadow-xl transition">
                        <div class="text-4xl mb-4">👥</div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Gestión de Personal</h3>
                        <p class="text-gray-600">
                            Administra médicos, enfermeras y personal administrativo con roles y permisos personalizados.
                        </p>
                    </div>
                    
                    <!-- Característica 4 -->
                    <div class="bg-gradient-to-br from-yellow-50 to-orange-50 p-6 rounded-lg shadow-md hover:shadow-xl transition">
                        <div class="text-4xl mb-4">💰</div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Control de Pagos</h3>
                        <p class="text-gray-600">
                            Gestiona pagos, planes de cuotas y métodos de pago de manera eficiente.
                        </p>
                    </div>
                    
                    <!-- Característica 5 -->
                    <div class="bg-gradient-to-br from-cyan-50 to-blue-50 p-6 rounded-lg shadow-md hover:shadow-xl transition">
                        <div class="text-4xl mb-4">📊</div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Reportes y Estadísticas</h3>
                        <p class="text-gray-600">
                            Genera reportes detallados y analiza estadísticas de tu práctica médica.
                        </p>
                    </div>
                    
                    <!-- Característica 6 -->
                    <div class="bg-gradient-to-br from-red-50 to-rose-50 p-6 rounded-lg shadow-md hover:shadow-xl transition">
                        <div class="text-4xl mb-4">🔒</div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Seguridad y Confidencialidad</h3>
                        <p class="text-gray-600">
                            Protección de datos con las mejores técnicas de seguridad y encriptación.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Sección Ventajas -->
        <section id="ventajas" class="py-20 bg-gradient-to-b from-indigo-50 to-blue-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl md:text-4xl font-bold text-center text-gray-900 mb-4">
                    ¿Por qué utilizar nuestro sistema?
                </h2>
                <p class="text-center text-gray-600 mb-12 text-lg">
                    Porque Ofrece Muchas Ventajas
                </p>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="text-center p-6 bg-white rounded-lg shadow-md">
                        <div class="text-5xl mb-4">✅</div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Seguro</h3>
                        <p class="text-gray-600 text-sm">Protección de datos de nivel empresarial</p>
                    </div>
                    <div class="text-center p-6 bg-white rounded-lg shadow-md">
                        <div class="text-5xl mb-4">🔄</div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Confiable</h3>
                        <p class="text-gray-600 text-sm">Sistema estable y disponible 24/7</p>
                    </div>
                    <div class="text-center p-6 bg-white rounded-lg shadow-md">
                        <div class="text-5xl mb-4">🎯</div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Fácil de usar</h3>
                        <p class="text-gray-600 text-sm">Interfaz intuitiva y amigable</p>
                    </div>
                    <div class="text-center p-6 bg-white rounded-lg shadow-md">
                        <div class="text-5xl mb-4">💻</div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Accesible</h3>
                        <p class="text-gray-600 text-sm">Acceso desde cualquier dispositivo</p>
                    </div>
                </div>
                <div class="mt-12 text-center">
                    <p class="text-lg text-gray-700 mb-4">
                        Puedes acceder a la información desde cualquier parte del mundo, de forma segura y sencilla.
                    </p>
                    <p class="text-lg text-gray-700">
                        Te ahorrarás tiempo y espacio en tu oficina y podrás registrar y analizar la información como nunca antes.
                    </p>
                </div>
            </div>
        </section>

        <!-- Sección Testimonios -->
        <section id="testimonios" class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl md:text-4xl font-bold text-center text-gray-900 mb-12">
                    Testimonios
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Testimonio 1 -->
                    <div class="bg-gray-50 p-6 rounded-lg shadow-md">
                        <p class="text-gray-700 mb-4 italic">
                            "El sistema ha sido una herramienta indispensable en el buen funcionamiento de mi consulta, con información actualizada y completa, fácil de usar y de fácil acceso, lo cual la hace sumamente valiosa en mi día a día."
                        </p>
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-indigo-600 rounded-full flex items-center justify-center text-white font-bold mr-4">
                                JA
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">Dr. Joaquín Acuña Mora</p>
                                <p class="text-sm text-gray-600">Neonatología</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Testimonio 2 -->
                    <div class="bg-gray-50 p-6 rounded-lg shadow-md">
                        <p class="text-gray-700 mb-4 italic">
                            "Tengo varios años de utilizar el sistema y nunca me ha fallado. Ha sido una herramienta que me ha facilitado organizar mis expedientes, nunca he perdido información y puedo guardar exámenes de laboratorio rápidamente."
                        </p>
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-green-600 rounded-full flex items-center justify-center text-white font-bold mr-4">
                                FA
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">Dra. Fabiola Acón Rojas</p>
                                <p class="text-sm text-gray-600">Especialista en Pediatría</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Testimonio 3 -->
                    <div class="bg-gray-50 p-6 rounded-lg shadow-md">
                        <p class="text-gray-700 mb-4 italic">
                            "La experiencia ha sido excelente. El sistema se adapta a mis necesidades, muy ordenado, fácil de usar y no he tenido ningún problema técnico."
                        </p>
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-purple-600 rounded-full flex items-center justify-center text-white font-bold mr-4">
                                AV
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">Dra. Adriana Vargas González</p>
                                <p class="text-sm text-gray-600">Odontología Pediátrica</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Testimonio 4 -->
                    <div class="bg-gray-50 p-6 rounded-lg shadow-md">
                        <p class="text-gray-700 mb-4 italic">
                            "Ha sido una excelente herramienta para mejorar el orden en el manejo de mis pacientes. Me ha permitido crear expedientes electrónicos con información completa y a mi alcance en cualquier momento."
                        </p>
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold mr-4">
                                AA
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">Dr. Armando Alfaro Ramírez</p>
                                <p class="text-sm text-gray-600">Cardiólogo y Electrofisiólogo Pediatra</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Sección Noticias -->
        <section id="noticias" class="py-20 bg-gradient-to-b from-gray-50 to-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl md:text-4xl font-bold text-center text-gray-900 mb-4">
                    Noticias
                </h2>
                <p class="text-center text-gray-600 mb-12 text-lg">
                    Lo último en nuestro sistema
                </p>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <!-- Noticia 1 -->
                    <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-xl transition">
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Acceso a la Información</h3>
                        <p class="text-gray-600 mb-4">
                            En la era de la información, es indispensable para los médicos tener acceso a los datos de sus pacientes en cualquier momento o lugar.
                        </p>
                        <a href="#" class="text-indigo-600 hover:text-indigo-800 font-semibold">Ver más →</a>
                    </div>
                    
                    <!-- Noticia 2 -->
                    <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-xl transition">
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Simple es Mejor</h3>
                        <p class="text-gray-600 mb-4">
                            En la actualidad, existe una gran variedad de programas de cómputo. La simplicidad y facilidad de uso son clave para la productividad.
                        </p>
                        <a href="#" class="text-indigo-600 hover:text-indigo-800 font-semibold">Ver más →</a>
                    </div>
                    
                    <!-- Noticia 3 -->
                    <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-xl transition">
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Optimización del Espacio</h3>
                        <p class="text-gray-600 mb-4">
                            Los médicos buscan formas más eficientes para atender a sus pacientes y mejorar su consulta. La digitalización es la respuesta.
                        </p>
                        <a href="#" class="text-indigo-600 hover:text-indigo-800 font-semibold">Ver más →</a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Sección Contacto -->
        <section id="contacto" class="py-20 bg-indigo-600 text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl md:text-4xl font-bold text-center mb-12">
                    Contáctenos
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                    <!-- Información de Contacto -->
                    <div>
                        <h3 class="text-2xl font-semibold mb-6">Información de Contacto</h3>
                        <div class="space-y-4">
                            <div class="flex items-start">
                                <div class="text-2xl mr-4">📧</div>
                                <div>
                                    <p class="font-semibold">Email</p>
                                    <p class="text-indigo-200">contacto@consultoriomedicoyeshua.com</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="text-2xl mr-4">📞</div>
                                <div>
                                    <p class="font-semibold">Teléfono</p>
                                    <p class="text-indigo-200">+591 123 456 789</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="text-2xl mr-4">📍</div>
                                <div>
                                    <p class="font-semibold">Dirección</p>
                                    <p class="text-indigo-200">Santa Cruz, Bolivia</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Formulario de Contacto -->
                    <div>
                        <h3 class="text-2xl font-semibold mb-6">Envíanos un Mensaje</h3>
                        <form @submit.prevent="enviarContacto" class="space-y-4">
                            <div>
                                <InputLabel for="nombre" value="Nombre" class="text-white" />
                                <TextInput
                                    id="nombre"
                                    type="text"
                                    v-model="formularioContacto.nombre"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError :message="formularioContacto.errors.nombre" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="email" value="Email" class="text-white" />
                                <TextInput
                                    id="email"
                                    type="email"
                                    v-model="formularioContacto.email"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError :message="formularioContacto.errors.email" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="telefono" value="Teléfono" class="text-white" />
                                <TextInput
                                    id="telefono"
                                    type="tel"
                                    v-model="formularioContacto.telefono"
                                    class="mt-1 block w-full"
                                />
                                <InputError :message="formularioContacto.errors.telefono" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="mensaje" value="Mensaje" class="text-white" />
                                <textarea
                                    id="mensaje"
                                    v-model="formularioContacto.mensaje"
                                    rows="4"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required
                                ></textarea>
                                <InputError :message="formularioContacto.errors.mensaje" class="mt-2" />
                            </div>
                            <PrimaryButton
                                type="submit"
                                :disabled="formularioContacto.processing"
                                class="w-full bg-white text-indigo-600 hover:bg-indigo-50"
                            >
                                Enviar Mensaje
                            </PrimaryButton>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-gray-900 text-white py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <div>
                        <h4 class="text-lg font-semibold mb-4">Consultorio Medico Yeshua</h4>
                        <p class="text-gray-400 text-sm">
                            Sistema integral de gestión médica para profesionales de la salud.
                        </p>
                    </div>
                    <div>
                        <h4 class="text-lg font-semibold mb-4">Enlaces</h4>
                        <ul class="space-y-2 text-sm text-gray-400">
                            <li><a href="#inicio" class="hover:text-white">Inicio</a></li>
                            <li><a href="#caracteristicas" class="hover:text-white">Características</a></li>
                            <li><a href="#ventajas" class="hover:text-white">Ventajas</a></li>
                            <li><a href="#testimonios" class="hover:text-white">Testimonios</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-lg font-semibold mb-4">Soporte</h4>
                        <ul class="space-y-2 text-sm text-gray-400">
                            <li><a href="#contacto" class="hover:text-white">Contacto</a></li>
                            <li><a href="#" class="hover:text-white">Preguntas Frecuentes</a></li>
                            <li><a href="#" class="hover:text-white">Políticas de Privacidad</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-lg font-semibold mb-4">Síguenos</h4>
                        <div class="flex space-x-4">
                            <a href="#" class="text-gray-400 hover:text-white">Facebook</a>
                            <a href="#" class="text-gray-400 hover:text-white">Twitter</a>
                            <a href="#" class="text-gray-400 hover:text-white">LinkedIn</a>
                        </div>
                    </div>
                </div>
                <div class="mt-8 pt-8 border-t border-gray-800 text-center text-sm text-gray-400">
                    <p>&copy; {{ new Date().getFullYear() }} Consultorio Medico Yeshua. Todos los derechos reservados.</p>
                </div>
            </div>
        </footer>
    </div>
</template>
