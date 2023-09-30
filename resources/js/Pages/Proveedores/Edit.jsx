import React from "react";
import Authenticated from "@/Layouts/AuthenticatedLayout";
import { Head, useForm, Link, usePage } from "@inertiajs/react";
import InputError from "@/Components/InputError";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faBackward } from "@fortawesome/free-solid-svg-icons";
import { faSave } from "@fortawesome/free-solid-svg-icons";

export default function Dashboard(props) {
    const { proveedor } = usePage().props;

    const { data, setData, put, errors, processing } = useForm({
        nombreEmpresa: proveedor.nombreEmpresa||"",
        nombreContacto: proveedor.nombreContacto||"",
        direccion:proveedor.direccion|| "",
        telefono: proveedor.telefono ||"",
        email: proveedor.email||"",
        sitioWeb:proveedor.sitioWeb|| "",
        ruc: proveedor.ruc||"",
        tipoProveedor:proveedor.tipoProveedor|| "",
        descripcion: proveedor.descripcion||"",
        tipoCuenta: proveedor.tipoCuenta||"",
    });

    function handleSubmit(e) {
        e.preventDefault();
        // console.log(data);
        put(route("proveedores.update", proveedor.id));
    }

    return (
        <Authenticated
            auth={props.auth}
            errors={props.errors}
            header={
                <h2 className="sm:ml-0 ml-3 font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Editar Proveedor
                </h2>
            }
        >
            <Head title="Proveedores" />

            <div className="py-8">
                <div className="max-w-8xl mx-auto px-6 lg:px-8">
                    <div className="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                        <div className="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                            <div className="flex items-center justify-between mb-6">
                                <Link
                                    className="px-6 py-2 text-white bg-blue-500 hover:bg-blue-600 rounded-md focus:outline-none"
                                    href={route("proveedores.index")}
                                >
                                    <FontAwesomeIcon
                                        icon={faBackward}
                                        className="mr-2"
                                    />
                                    Regresar
                                </Link>
                            </div>

                            <form name="createForm" onSubmit={handleSubmit}>
                                <div className="flex flex-col">
                                    <div className="mb-4">
                                        <label className="text-gray-900 dark:text-gray-100">
                                            Nombre de la Empresa
                                            <span className="text-red-500 ml-1">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            className="mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                            label="Nombre de la Empresa"
                                            name="nombreEmpresa"
                                            autoFocus
                                            value={data.nombreEmpresa}
                                            onChange={(e) =>
                                                setData(
                                                    "nombreEmpresa",
                                                    e.target.value
                                                )
                                            }
                                        />
                                        <InputError
                                            message={errors.nombreEmpresa}
                                            className="mt-2"
                                        />
                                    </div>
                                    <div className="mb-4">
                                        <label className="text-gray-900 dark:text-gray-100">
                                            Nombre Contacto
                                        </label>
                                        <input
                                            type="text"
                                            className="mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                            label="NombreContacto"
                                            name="nombreContacto"
                                            value={data.nombreContacto}
                                            onChange={(e) =>
                                                setData(
                                                    "nombreContacto",
                                                    e.target.value
                                                )
                                            }
                                        />
                                        <InputError
                                            message={errors.nombreContacto}
                                            className="mt-2"
                                        />
                                    </div>
                                    <div className="mb-4 sm:flex block">
                                        <div className="w-full">
                                            <label className="text-gray-900 dark:text-gray-100">
                                                RUC
                                            </label>
                                            <input
                                                type="text"
                                                className="mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                                label="RUC"
                                                name="ruc"
                                                maxLength="11"
                                                value={data.ruc}
                                                onChange={(e) =>
                                                    setData("ruc", e.target.value)
                                                }
                                            />
                                            <InputError
                                                message={errors.ruc}
                                                className="mt-2"
                                            />
                                        </div>
                                    </div>
                                    <div className="mb-4 sm:flex block">
                                        <div className="w-full sm:mb-0 mb-4 mr-2">
                                            <label className="text-gray-900 dark:text-gray-100">
                                                Teléfono
                                            </label>
                                            <input
                                                type="tel"
                                                className="mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                                label="Telefono"
                                                name="telefono"
                                                value={data.telefono}
                                                onChange={(e) =>
                                                    setData(
                                                        "telefono",
                                                        e.target.value
                                                    )
                                                }
                                            />
                                            <InputError
                                                message={errors.telefono}
                                                className="mt-2"
                                            />
                                        </div>
                                        <div className="w-full">
                                            <label className="text-gray-900 dark:text-gray-100">
                                                Email
                                            </label>
                                            <input
                                                type="email"
                                                className="mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                                label="email"
                                                name="email"
                                                autoComplete="email"
                                                value={data.email}
                                                onChange={(e) =>
                                                    setData(
                                                        "email",
                                                        e.target.value
                                                    )
                                                }
                                            />
                                            <InputError
                                                message={errors.email}
                                                className="mt-2"
                                            />
                                        </div>
                                    </div>
                                    <div className="mb-4">
                                        <label className="text-gray-900 dark:text-gray-100">
                                            Dirección
                                        </label>
                                        <input
                                            type="text"
                                            className="mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                            label="Dirección"
                                            name="direccion"
                                            value={data.direccion}
                                            onChange={(e) =>
                                                setData(
                                                    "direccion",
                                                    e.target.value
                                                )
                                            }
                                        />
                                        <InputError
                                            message={errors.direccion}
                                            className="mt-2"
                                        />
                                    </div>

                                    <div className="mb-4">
                                        <label className="text-gray-900 dark:text-gray-100">
                                            Sitio Web
                                        </label>
                                        <input
                                            type="text"
                                            className="mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                            label="sitioWeb"
                                            name="sitioWeb"
                                            value={data.sitioWeb}
                                            onChange={(e) =>
                                                setData(
                                                    "sitioWeb",
                                                    e.target.value
                                                )
                                            }
                                        />
                                        <InputError
                                            message={errors.sitioWeb}
                                            className="mt-2"
                                        />
                                    </div>
                                    <div className="mb-4">
                                        <label className="text-gray-900 dark:text-gray-100">
                                            Tipo de Proveedor
                                        </label>
                                        <input
                                            type="text"
                                            className="mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                            label="Tipo de Proveedor"
                                            name="tipoProveedor"
                                            value={data.tipoProveedor}
                                            onChange={(e) =>
                                                setData(
                                                    "tipoProveedor",
                                                    e.target.value
                                                )
                                            }
                                        />
                                        <InputError
                                            message={errors.tipoProveedor}
                                            className="mt-2"
                                        />
                                    </div>
                                    <div className="mb-4">
                                        <label className="text-gray-900 dark:text-gray-100">
                                            Descripción
                                        </label>
                                        <input
                                            type="text"
                                            className="mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                            label="descripcion"
                                            name="descripcion"
                                            value={data.descripcion}
                                            onChange={(e) =>
                                                setData(
                                                    "descripcion",
                                                    e.target.value
                                                )
                                            }
                                        />
                                        <InputError
                                            message={errors.descripcion}
                                            className="mt-2"
                                        />
                                    </div>
                                    <div className="mb-4">
                                        <label className="text-gray-900 dark:text-gray-100">
                                            Tipo de Pago o Cuenta
                                        </label>
                                        <input
                                            type="text"
                                            className="mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                            label="tipoCuenta"
                                            name="tipoCuenta"
                                            value={data.tipoCuenta}
                                            onChange={(e) =>
                                                setData(
                                                    "tipoCuenta",
                                                    e.target.value
                                                )
                                            }
                                        />
                                        <InputError
                                            message={errors.tipoCuenta}
                                            className="mt-2"
                                        />
                                    </div>
                                </div>
                                <div className="mt-4">
                                    <button
                                        type="submit"
                                        disabled={processing}
                                        className={`px-7 py-2 font-bold text-white bg-green-500 hover:bg-green-600 rounded ${
                                            processing && "opacity-25"
                                        }`}
                                    >
                                        <FontAwesomeIcon
                                            icon={faSave}
                                            className="mr-2"
                                        />
                                        Guardar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </Authenticated>
    );
}
