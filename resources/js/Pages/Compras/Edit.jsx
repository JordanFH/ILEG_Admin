import React, {useEffect, useState} from "react";
import Authenticated from "@/Layouts/AuthenticatedLayout";
import {Head, useForm, Link, usePage} from "@inertiajs/react";
import InputError from "@/Components/InputError";
import {FontAwesomeIcon} from "@fortawesome/react-fontawesome";
import {faBackward, faPlus, faPrint, faTrashAlt} from "@fortawesome/free-solid-svg-icons";
import {faSave} from "@fortawesome/free-solid-svg-icons";
import CurrencyInput from "@/Components/CurrencyInput";
import axios from "axios";
import Modal from "@/Components/Modal";

export default function Dashboard(props) {
    const {compra, proveedores, productos, detalle_compras,user} = usePage().props;
    const fechaActual = new Date();
    const fechaString = fechaActual.getFullYear() + '-' + (fechaActual.getMonth() + 1).toString().padStart(2, '0') + '-' + fechaActual.getDate().toString().padStart(2, '0');

    const {
        data: detallePedidoData,
        setData: setDetallePedidoData,
        post: postDetallePedido,
        errors: errorsDetallePedido,
        processing: processingDetallePedido
    } = useForm({
         item_id: "", precio_unitario: 0, cantidad: 1, monto: 0, stock: 0,
    });

    function handlePedidoSubmit(e) {
        e.preventDefault();
        putPedido(route("compras.update", compra.id));
    }

    const proveedoresArray = proveedores.data.map((proveedor) => ({
        id: proveedor.id, nombre: proveedor.nombreEmpresa,
    }));

    const metodosPagoArray = [
        'Efectivo',
        'Crédito',
    ]

    const productosArray = productos && productos.data ? productos.data.map((producto) => ({
        id: producto.id, nombre: producto.nombre, precio: producto.precio, stock: producto.stock,
    })) : [];

    const detallesArray = detalle_compras && detalle_compras.data ? detalle_compras.data.map((detalle) => {
        const elementoEncontrado = productosArray.find(producto => producto.id === detalle.item_id)

        return {
            id: detalle.id,
            compra_id: detalle.compra_id,
            monto: detalle.monto,
            name: elementoEncontrado ? elementoEncontrado.nombre : "Elemento no encontrado"
        };
    }) : [];

    function roundToTwo(num) {
        // Usamos parseFloat para asegurarnos de que num sea tratado como un número.
        const multiplier = Math.pow(10, 2);
        const rounded = Math.round(num * multiplier) / multiplier;
        return isNaN(rounded) ? 0 : rounded; // Manejamos el caso de NaN si num no es un número válido.
    }

    let totalGeneral = 0;
    detallesArray.forEach(detalle => {
        const totalDetalle = detalle.monto;
        totalGeneral += totalDetalle/1.18;
    });
    const {
        data: pedidoData, setData: setPedidoData, errors: errorsPedido, put: putPedido, processing: processingPedido
    } = useForm({
        proveedor_id: compra.proveedor_id || "",
        fecha: compra.fecha || fechaString,
        subtotal: compra.subtotal || 0,
        impuestos: compra.impuestos,
        total: compra.total || 0,
        monto_pagado: compra.monto_pagado || 0,
        estado: compra.estado || "Pendiente",
        descripcion: compra.descripcion || "",
        metodo_pago: compra.metodo_pago || "",
    });


    const [detallePedidos, setDetallePedidos] = useState(detallesArray);
    const [isModalCreateOpen, setIsModalCreateOpen] = useState(false);
    const [isButtonDisabled, setIsButtonDisabled] = useState(false);

    const destroyDetalle = async (id) => {
        try {

            const response = await axios.delete(route("compradetalles.destroy", id));
            console.log("Respuesta de la solicitud DELETE:", response.data)
            // Después de eliminar el elemento, actualiza la lista de detalles filtrando el elemento eliminado.
            setDetallePedidos(prevDetallePedidos => prevDetallePedidos.filter(detalle => detalle.id !== response.data.id));


            // Actualiza los datos del pedido
            setPedidoData(prevData => ({
                ...prevData,
                subtotal: pedidoData.subtotal - response.data.monto/1.18,
                impuestos: pedidoData.impuestos - response.data.monto/1.18 * 0.18,
                total: pedidoData.total - response.data.monto,
            }));
            //window.location.reload();
        } catch (error) {
            // Manejar el error
            console.log(error);
        }
    };


    async function handleDetallePedidoSubmit(e) {
        e.preventDefault();
        try{

            console.log("detallePedidoData", detallePedidoData);
            const response = await axios.post(route("compradetalles.storeDetalle", { id: compra.id }), detallePedidoData);

            console.log("Respuesta de la solicitud POST:", response.data)
            const newDetalle = response.data;

            if(newDetalle === "el item_id es null"){
                console.log("el item_id es null");
            }
            else if(newDetalle === "el stock es 0"){
                console.log("el stock es 0");
            }else{
                // Convierte el nuevo detalle a formato deseado
                const itemId = parseInt(newDetalle.item_id);
                const elementoEncontrado = productosArray.find(producto => producto.id === itemId)


                const newDetalleM = {
                    id: newDetalle.id,
                    compra_id: newDetalle.compra_id,
                    monto: newDetalle.monto,
                    name: elementoEncontrado ? elementoEncontrado.nombre : "Elemento no encontrado"
                };

                // Agrega el nuevo detalle a la lista de detalles
                const updated = [...detallePedidos, newDetalleM];
                setDetallePedidos(updated);

                // Actualiza los datos del pedido
                setPedidoData(prevData => ({
                    ...prevData,
                    subtotal: pedidoData.subtotal + newDetalleM.monto/1.18,
                    impuestos: pedidoData.impuestos + newDetalleM.monto/1.18 * 0.18,
                    total: pedidoData.total + newDetalleM.monto,
                }));
            }

        }
        catch (error) {
            console.error(error);
        }
        setDetallePedidoData({
            precio_unitario: 0,
            cantidad: 1,
            monto: 0,
        }); //elmonto a pagar es de prueba
    }



    const openModalCreate = () => {
        setIsModalCreateOpen(true);
    };

    const closeModalCreate = () => {
        setIsModalCreateOpen(false);
    };


    useEffect(() => {
        // Calculamos los impuestos cada vez que subtotal cambie
        const nuevoSubtotal = pedidoData.subtotal;
        console.log("nuevoSubtotal", nuevoSubtotal)
        const nuevoIGV = nuevoSubtotal * 0.18;
        console.log("nuevoIGvvv", nuevoSubtotal * 0.18)
        const totalAPagar = nuevoSubtotal + nuevoIGV;

        setPedidoData({
            ...pedidoData,
            impuestos: nuevoIGV,
            total: totalAPagar,
        });
        if (pedidoData.monto_pagado >= totalAPagar) {
            setPedidoData(prevData => ({...prevData, estado: "Cancelado"})); //estaba Cancelado
        } else {
            setPedidoData(prevData => ({...prevData, estado: "Pendiente"}));
        }

    }, [pedidoData.subtotal, pedidoData.impuestos, pedidoData.monto_pagado, pedidoData.total]);

    function formatCurrency(amount) {
        return "S/. " + amount.toLocaleString("es-PE", {
            minimumFractionDigits: 2, maximumFractionDigits: 2,
        });
    }

    // funcion para validar que el item_id no sea null
    function itemIsNull() {
        return detallePedidoData.item_id === null || detallePedidoData.item_id === "";
    }

    function sinIGV(amount) {
        const result = amount/1.18;
        return parseFloat(result.toFixed(2));
    }

    // useeffect para validar que el item_id no sea null
    useEffect(() => {
        setIsButtonDisabled(itemIsNull());
    }, [detallePedidoData.item_id]);

    return (<Authenticated
        auth={props.auth}
        errors={props.errors}
        header={<h2 className="sm:ml-0 ml-3 font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Editar Compra
        </h2>}
    >
        <Head title="Compras"/>

        <div className="py-8">
            <div className="max-w-8xl mx-auto px-6 lg:px-8">
                <div className="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div className="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                        <div className="flex items-center justify-between mb-6">
                            <Link
                                className="px-6 py-2 text-white bg-blue-500 hover:bg-blue-600 rounded-md focus:outline-none"
                                href={route("compras.index")}
                            >
                                <FontAwesomeIcon
                                    icon={faBackward}
                                    className="mr-2"
                                />
                                Regresar
                            </Link>
                            <br/>
                            <div className="flex justify-between space-x-4">
                                <button type="button"
                                        className="text-rose-500 hover:text-white border border-rose-500 hover:bg-rose-500 focus:outline-none font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:border-rose-500 dark:text-rose-500 dark:hover:text-white dark:hover:bg-rose-500">
                                    <FontAwesomeIcon icon={faPrint} className="md:mr-2 mr-0"/>
                                    <span className="md:inline-block hidden">Imprimir</span>
                                </button>
                            </div>
                        </div>

                        <form name="createForm" onSubmit={handlePedidoSubmit}>
                            <div className="lg:flex block">
                                <div className="flex flex-col w-full mr-4">
                                    <div className="mb-4">
                                        <label className="text-gray-900 dark:text-gray-100">
                                            Proveedor
                                            <span className="text-red-500 ml-1">*</span>
                                        </label>
                                        <select
                                            name="proveedor_id"
                                            onChange={(e) => {
                                                setPedidoData((prevData) => ({
                                                    ...prevData, proveedor_id: e.target.value
                                                }));
                                                setDetallePedidoData("proveedor_id", e.target.value);
                                            }}

                                            value={pedidoData.proveedor_id}
                                            className="mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                        >
                                            <option value="">
                                                Seleccionar
                                            </option>
                                            {proveedoresArray
                                                .map(({id, nombre}) => (<option key={id} value={id}>
                                                    {nombre}
                                                </option>))}
                                        </select>
                                        <InputError
                                            message={errorsPedido.proveedor_id}
                                            className="mt-2"
                                        />
                                    </div>
                                </div>
                                <div className="flex flex-col w-full">
                                    <div className="mb-4">
                                        <label className="text-gray-900 dark:text-gray-100">
                                            Usuario
                                        </label>
                                        <input
                                            type="text"
                                            className="cursor-not-allowed mt-1 w-full border-gray-300 dark:border-gray-600 bg-gray-200 dark:bg-gray-800 dark:text-gray-200 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                            label="Nombre y Apellidos"
                                            name="usuario_id"
                                            autoFocus
                                            disabled={true}
                                            value={
                                                user.name
                                            }
                                        />
                                    </div>
                                </div>
                            </div>
                            <div className="flex flex-col w-full">
                                <div className="mb-4">
                                    <label className="text-gray-900 dark:text-gray-100">
                                        Fecha
                                        <span className="text-red-500 ml-1">*</span>
                                    </label>
                                    <input
                                        type="date"
                                        className="mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                        label="Fecha"
                                        name="fecha"
                                        value={pedidoData.fecha}
                                        onChange={(e) => setPedidoData("fecha", e.target.value)}
                                    />
                                    <InputError
                                        message={errorsPedido.fecha}
                                        className="mt-2"
                                    />
                                </div>
                            </div>
                            <div className="flex flex-col w-full">
                                <div className="mb-16">
                                    <label className="text-gray-900 dark:text-gray-100">
                                        Detalle de la compra
                                        <span className="text-red-500 ml-1">*</span>
                                    </label>

                                    <div
                                        className="rounded-lg border border-gray-300 dark:border-gray-700 relative overflow-x-auto">
                                        <table className="w-full border-collapse table-fixed">
                                            <thead>
                                            <tr className="border-b border-gray-300 dark:border-gray-700 dark:bg-gray-900 bg-gray-200">
                                                <th className="uppercase text-center border-r border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 px-4 py-3 w-20">
                                                    N°
                                                </th>
                                                <th className="uppercase border-r border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 px-4 py-3 w-60">
                                                    Producto
                                                </th>
                                                <th className="uppercase text-center border-r border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 px-4 py-3 w-40">
                                                    Monto
                                                </th>
                                                <th className="uppercase text-center border-r border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 px-4 py-3 w-40">
                                                    Monto Sin IGV
                                                </th>
                                                <th className="uppercase border-r border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 px-3 py-3 w-20">
                                                    Quitar
                                                </th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            {detallePedidos.map(({id, monto,name}, index) => (<tr
                                                className="[&:not(:last-child)]:border-b dark:border-gray-700 border-gray-300"
                                                key={index}
                                            >
                                                <td className="border-r text-center dark:border-gray-700 border-gray-300 px-4 py-2 text-gray-900 dark:text-gray-100">
                                                    {index + 1}
                                                </td>
                                                <td className="border-r truncate dark:border-gray-700 border-gray-300 px-4 py-2 text-gray-900 dark:text-gray-100">
                                                    {
                                                        name
                                                    }
                                                </td>
                                                <td className="border-r text-center truncate dark:border-gray-700 border-gray-300 px-4 py-2 text-gray-900 dark:text-gray-100">
                                                    {formatCurrency(monto)}
                                                </td>
                                                <td className="border-r text-center truncate dark:border-gray-700 border-gray-300 px-4 py-2 text-gray-900 dark:text-gray-100">
                                                    {formatCurrency(sinIGV(monto))}
                                                </td>
                                                <td className="text-center dark:border-gray-700 border-gray-300 px-4 py-2">
                                                    <button
                                                        onClick={() => {
                                                            destroyDetalle(id);
                                                        }}
                                                        /*disabled={isButtonDisabled}*/
                                                        tabIndex="-1"
                                                        type="button"
                                                        className={`w-15 sm:w-auto m-1 px-4 py-2 text-sm text-gray-700 dark:text-gray-100 hover:text-red-500 hover:dark:text-red-500`}
                                                    >
                                                        <FontAwesomeIcon
                                                            className="text-base"
                                                            icon={faTrashAlt}
                                                        />
                                                    </button>
                                                </td>
                                            </tr>))}
                                            {detallePedidos.length === 0 && (<tr>
                                                <td
                                                    className="px-6 py-4 text-center px-4 py-2 text-gray-900 dark:text-gray-100"
                                                    colSpan="5"
                                                >
                                                    No hay items agregados.
                                                </td>
                                            </tr>)}
                                            </tbody>
                                        </table>
                                    </div>

                                    <div className="mt-6 text-center">
                                        <button
                                            onClick={() => {
                                                /*setIdSeleccionado(
                                                    id
                                                );
                                                setItemSeleccionado(
                                                    nombre
                                                );*/
                                                openModalCreate();
                                            }}
                                            tabIndex="-1"
                                            type="button"
                                            className="px-6 py-3 text-white bg-purple-500 rounded-full focus:outline-none hover:bg-purple-600"
                                        >
                                            Nuevo
                                            <FontAwesomeIcon
                                                icon={faPlus}
                                                className="ml-2"
                                                beat={true}
                                            />
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div className="lg:flex block">
                                <div className="flex flex-col w-full mr-4">
                                    <div className="mb-4">
                                        <label className="text-gray-900 dark:text-gray-100">
                                            Monto a Pagar (Soles)
                                            <span className="text-red-500 ml-1">*</span>
                                        </label>
                                        <CurrencyInput
                                            placeholder="0.00"
                                            value={pedidoData.monto_pagado}
                                            onChange={(e) => {
                                                const inputValue = e.target.value;
                                                setPedidoData(prevData => ({
                                                    ...prevData, monto_pagado: inputValue.replace(",", "")
                                                }));
                                            }}
                                            required
                                            type="text"
                                            className="mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                        />
                                        <InputError
                                            message={errorsPedido.monto_pagado}
                                            className="mt-2"
                                        />
                                    </div>
                                    <div className="mb-4">
                                        <label className="text-gray-900 dark:text-gray-100">
                                            Estado
                                            <span className="text-red-500 ml-1">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            className="font-black cursor-not-allowed mt-1 w-full border-gray-300 dark:border-gray-600 bg-gray-200 dark:bg-gray-800 dark:text-gray-200 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                            label="Estado"
                                            name="estado"
                                            value={pedidoData.estado}
                                            onChange={(e) => setPedidoData("estado", e.target.value)}
                                            disabled={true}
                                            style={{color: pedidoData.estado === "Pendiente" ? "#ef4444" : "#22c55e"}}
                                        />
                                        <InputError
                                            message={errorsPedido.estado}
                                            className="mt-2"
                                        />
                                    </div>
                                    <div className="mb-4">
                                        <label className="text-gray-900 dark:text-gray-100">
                                            Descripción
                                        </label>
                                        <input
                                            maxLength={150}
                                            placeholder="Opcional"
                                            value={pedidoData.descripcion}
                                            onChange={(e) => {
                                                const inputValue = e.target.value;
                                                setPedidoData(prevData => ({
                                                    ...prevData, descripcion: inputValue
                                                }));
                                            }}
                                            type="text"
                                            className="mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                        />
                                        <InputError
                                            message={errorsPedido.descripcion}
                                            className="mt-2"
                                        />
                                    </div>
                                    <div className="mt-4">
                                        <label
                                            className="text-gray-900 dark:text-gray-100">
                                            Método de Pago de la compra
                                        </label>
                                        <select
                                            value={pedidoData.metodo_pago}
                                            onChange={(e) => {
                                                const selectedOption = e.target.value; // Obtiene el valor seleccionado directamente

                                                const newPedidoData = {
                                                    ...pedidoData,
                                                    metodo_pago: selectedOption, // Asigna el valor seleccionado al campo "metodo_pago"
                                                };

                                                setPedidoData(newPedidoData);
                                            }}
                                            className="mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                        >
                                            <option value="">
                                                Seleccionar
                                            </option>
                                            {metodosPagoArray.map((nombre) => (
                                                <option key={nombre} value={nombre}>
                                                    {nombre}
                                                </option>
                                            ))}
                                        </select>
                                        <InputError
                                            message={errorsDetallePedido.item_id}
                                            className="mt-2"
                                        />
                                    </div>
                                </div>

                                <div className="flex flex-col w-full">
                                    <div className="mb-4">
                                        <label className="text-gray-900 dark:text-gray-100">
                                            Subtotal (Soles)
                                            <span className="text-red-500 ml-1">*</span>
                                        </label>
                                        <CurrencyInput
                                            placeholder="0.00"
                                            value={roundToTwo(pedidoData.total- pedidoData.impuestos)}
                                            onChange={(e) => {
                                                const inputValue = e.target.value;
                                                setPedidoData("subtotal", inputValue.replace(",", ""));
                                            }}
                                            required
                                            disabled={true}
                                            type="text"
                                            className="cursor-not-allowed mt-1 w-full border-gray-300 dark:border-gray-600 bg-gray-200 dark:bg-gray-800 dark:text-gray-200 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                        />
                                        <InputError
                                            message={errorsPedido.subtotal}
                                            className="mt-2"
                                        />
                                    </div>
                                    <div className="mb-4">
                                        <label className="text-gray-900 dark:text-gray-100">
                                            IGV (Soles)
                                            <span className="text-red-500 ml-1">*</span>
                                        </label>
                                        <CurrencyInput
                                            placeholder="0.00"
                                            value={roundToTwo(pedidoData.impuestos)}
                                            onChange={(e) => {
                                                const inputValue = e.target.value;
                                                setPedidoData("impuestos", inputValue.replace(",", ""));
                                            }}
                                            required
                                            disabled={true}
                                            type="text"
                                            className="cursor-not-allowed mt-1 w-full border-gray-300 dark:border-gray-600 bg-gray-200 dark:bg-gray-800 dark:text-gray-200 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                        />
                                        <InputError
                                            message={errorsPedido.impuestos}
                                            className="mt-2"
                                        />
                                    </div>
                                    <div className="mb-4">
                                        <label className="text-gray-900 dark:text-gray-100">
                                            TOTAL (Soles)
                                            <span className="text-red-500 ml-1">*</span>
                                        </label>
                                        <CurrencyInput
                                            placeholder="0.00"
                                            value={roundToTwo(pedidoData.total)}
                                            onChange={(e) => {
                                                const inputValue = e.target.value;
                                                setPedidoData("total", inputValue.replace(",", ""));
                                            }}
                                            required
                                            disabled={true}
                                            type="text"
                                            className="cursor-not-allowed mt-1 w-full border-2 border-blue-500 dark:border-blue-600 bg-gray-200 dark:bg-gray-800 text-gray-900 dark:text-gray-100 font-black rounded-md shadow-sm"
                                        />
                                        <InputError
                                            message={errorsPedido.total}
                                            className="mt-2"
                                        />
                                    </div>
                                </div>
                            </div>
                            <div className="mt-4">
                                <button
                                    type="submit"
                                    disabled={processingPedido}
                                    className={`px-7 py-2 font-bold text-white bg-green-500 hover:bg-green-600 rounded ${processingPedido && "opacity-25"}`}
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
        <Modal
            show={isModalCreateOpen}
            onClose={closeModalCreate}
        >
            <form onSubmit={handleDetallePedidoSubmit}>
                <div className="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div>
                        <div
                            className="mt-3 text-center sm:mt-0 sm:text-left">
                            <div className="sm:flex items-center">
                                <div className="mr-2">
                                    <div
                                        className="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-purple-100 sm:mx-0 sm:h-10 sm:w-10">
                                        <FontAwesomeIcon
                                            icon={faPlus}
                                            className="text-purple-700"
                                        />
                                    </div>
                                </div>
                                <h3 className="sm:mt-0 mt-4 text-lg font-medium text-gray-900 dark:text-gray-100">
                                    Añadiendo Producto
                                </h3>
                            </div>

                            <div className="mt-4">
                                <p className="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    A continuación vamos a añadir un nuevo
                                    producto a la compra
                                </p>
                            </div>
                            <div className="text-left">
                                <div className="mt-4">
                                    <label
                                        className="text-gray-900 dark:text-gray-100">
                                        Producto
                                    </label>
                                    <select
                                        value={detallePedidoData.item_id}
                                        onChange={(e) => {

                                            const selectedOption = productosArray.find((option) => option.id === parseInt(e.target.value));

                                            const newDetallePedidoData = {
                                                ...detallePedidoData, // Copiar todas las propiedades existentes
                                                monto: parseFloat(selectedOption.precio) * parseFloat(detallePedidoData.cantidad),
                                                item_id: e.target.value,
                                                precio_unitario: parseFloat(selectedOption.precio),
                                                stock: selectedOption.stock
                                            };

                                            setDetallePedidoData(newDetallePedidoData);
                                        }}
                                        className="mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                    >
                                        <option value="">
                                            Seleccionar
                                        </option>
                                        {productosArray
                                            .map(({id, nombre}) => (<option key={id} value={id}>
                                                {nombre}
                                            </option>))}
                                    </select>
                                    <InputError
                                        message={errorsDetallePedido.item_id}
                                        className="mt-2"
                                    />
                                </div>

                                <div className="flex justify-between mt-4">
                                    <div className="w-full">
                                        <label className="text-gray-900 dark:text-gray-100">
                                            Cantidad
                                            <span className="text-red-500 ml-1">*</span>
                                        </label>
                                        <input
                                            value={detallePedidoData.cantidad.toString()}
                                            onChange={(e) => {
                                                const inputValue = e.target.value;
                                                let newCantidad = 0;

                                                if (inputValue !== "") {
                                                    // Si hay un valor, intentamos convertirlo a número
                                                    const parsedValue = parseFloat(inputValue.replace(",", ""));

                                                    if (!isNaN(parsedValue)) {
                                                        // Si es un número válido, lo asignamos a newCantidad
                                                        newCantidad = parsedValue;
                                                    }
                                                }

                                                // Calculamos el nuevo monto en función de newCantidad
                                                const newMonto = newCantidad * parseFloat(detallePedidoData.precio_unitario);

                                                const newDetallePedidoData = {
                                                    ...detallePedidoData,
                                                    cantidad: newCantidad,
                                                    monto: newMonto,
                                                };

                                                setDetallePedidoData(newDetallePedidoData);

                                            }}
                                            required
                                            type="number" // Cambiado de "number" a "text"
                                            className="mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                            onKeyPress={(e) => {
                                                const allowedCharacters = /[0-9]*([,.][0-9]+)?/;
                                                if (!allowedCharacters.test(e.key)) {
                                                    e.preventDefault();
                                                }
                                            }}
                                        />
                                    </div>
                                </div>
                                <div className="mt-4">
                                    <label className="text-gray-900 dark:text-gray-100">
                                        Precio Unitario (Soles)
                                        <span className="text-red-500 ml-1">*</span>
                                    </label>

                                    <CurrencyInput
                                        placeholder=""
                                        value={detallePedidoData.precio_unitario}
                                        onChange={(e) => {
                                            const inputValue = e.target.value;
                                            // Verifica si el valor está vacío y establece cero en su lugar
                                            const newValue = inputValue === "" ? "0" : inputValue.replace(",", "");
                                            let newMonto = newValue * parseFloat(detallePedidoData.cantidad);
                                            //setDetallePedidoData("precio_unitario", newValue);

                                            const newDetallePedidoData = {
                                                ...detallePedidoData,
                                                precio_unitario: newValue,
                                                monto: newMonto,
                                            };

                                            setDetallePedidoData(newDetallePedidoData);
                                        }}
                                        required
                                        type="text"
                                        className="cursor-grabbing mt-1 w-full border-gray-300 dark:border-gray-600 bg-gray-200 dark:bg-gray-800 dark:text-gray-200 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                    />
                                    <InputError
                                        message={errorsDetallePedido.precio_unitario}
                                        className="mt-2"
                                    />
                                </div>
                                <div className="mt-4">
                                    <label className="text-gray-900 dark:text-gray-100">
                                        Monto (Soles)
                                        <span className="text-red-500 ml-1">*</span>
                                    </label>
                                    <CurrencyInput
                                        placeholder="0.00"
                                        value={detallePedidoData.monto}
                                        onChange={(e) => {
                                            const inputValue = e.target.value;
                                            setDetallePedidoData("monto", inputValue.replace(",", ""));
                                        }}
                                        required
                                        disabled={true}
                                        type="text"
                                        className="cursor-not-allowed mt-1 w-full border-gray-300 dark:border-gray-600 bg-gray-200 dark:bg-gray-800 dark:text-gray-200 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                    />
                                    <InputError
                                        message={errorsDetallePedido.monto}
                                        className="mt-2"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div
                    className="mt-1 mb-3 px-4 py-3 pb-4 sm:px-6 sm:flex sm:flex-row-reverse justify-center">
                    <button
                        type="submit"
                        disabled={isButtonDisabled}
                        onClick={() => {
                            // Recargar la página
                            closeModalCreate();
                        }}
                        className={`w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-purple-600 text-base font-medium text-white hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:ml-3 sm:w-auto sm:text-sm ${isButtonDisabled && "opacity-25"}`}
                    >
                        Aceptar
                    </button>
                    <button
                        onClick={() => {

                            closeModalCreate();
                        }}
                        type="button"
                        className="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                    >
                        Cancelar
                    </button>
                </div>
            </form>
        </Modal>
    </Authenticated>);
}
