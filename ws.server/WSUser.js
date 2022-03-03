//консоль с цветами, вместо нудного console.log() ;)
const consoleMsg = require('./consoleMsg');


//библотека для создания uuid
const uuid = require('uuid-random/index');

//дополнительные функции в другом файле
const functions = require('./additionalFunctions');

const { Order, Response } = require('../Modules/WSOrder');
//Константы типа пользователя
const TYPE_USER = 1;
const TYPE_EXECUTOR = 2;

const models = require('../Models/Models');


class WSUser {
    id = null;
    isOnline = false;
    user = null;
    order = null;
    responses = new Set();

    //Ивенты подключенные к этому ws
    events = {
    };

    //все интервалы должны быть добавлены сюда по примеру ниже.
    // wsUser.interval(function() {}, 1000));
    // Это позволит удалить их в случае если подключение будет потеряно и ws удалится
    intervals = new Map();

    //Аналогично с интервалами.
    timeouts = new Map();

    //комнаты к которым ws подключен
    rooms = new Set();

    //Тут при создании автоматически задаётся uuid.
    constructor(ws, user = null) {
        this.ws = ws;
        this.user = user;
        this.uuid = uuid();
        ws.onclose = this.onclose;
    }

    //Аналогия функции ws.on()
    on(event, callback) {
        this.ws.on(event, callback);
    }

    //Функция для создания ивента.
    addEventListener(event, callback) {
        this.events[event] = callback;
    }

    //Аналогия функции ws.emit()
    //NOTE: ws.emit() и ws.send() не одно и то же.
    // ws.emit() принимаем event как первый параметр
    // а ws.send() автоматически отправляет на event "message".
    emit(data) {
        this.ws.emit(data);
    }

    // Аналогия функции ws.send()
    send(data) {
        if (this.ws.readyState === 1)
            this.ws.send(data);
    }

    //Вызов ивента
    callEvent(data) {
        if (this.events[data.event]) {
            consoleMsg.info("[EVENT]" + data.event);
            this.events[data.event](data.data, data.event);
        }
        else this.ws.send(functions.errorResponse({"message": 'Event not found'}));
    }

    //парсит json
    static parseJson(json) {
        try {
            return JSON.parse(json);
        }
        catch (e) {
            consoleMsg.log('Cannot parse json');
            return false;
        }
    }

    //функция для создания интервала который будет подключен к массиву intervals
    interval(name, callback, timeout, args) {
        let interval = setInterval(callback, timeout, args);
        this.intervals.set(name, interval);
        return interval;
    }

    clearInterval(name) {
        if (this.intervals.has(name)) clearInterval(this.intervals.get(name));
    }

    //функция для создания таймаута который будет подключен к массиву timeouts
    timeout(name, callback, timeout, args) {
        let newTimeout = setTimeout(callback, timeout, args);
        this.timeouts.set(name, newTimeout);
        return newTimeout;
    }

    clearTimeout(name) {
        clearTimeout(this.timeouts.get(name));
    }

    //функция которая удаляет все интервалы таймауты и остальные данные
    destroy() {
        this.destroyIntervals();
        this.destroyTimeouts();
        this.destroyOrder();
        delete this.ws;
        delete this.user;
        delete this.events;
        delete this.uuid;
        delete this.isOnline;
    }

    //функция удаляет все интервалы
    destroyIntervals() {
        this.intervals.forEach(function(interval) {
            clearInterval(interval);
        });
        delete this.intervals;
        consoleMsg.info('Intervals destroyed');
    }

    //функция удаляет все таймауты
    destroyTimeouts() {
        this.timeouts.forEach(function(timeout) {
            clearTimeout(timeout);
        });
        consoleMsg.info('Timeouts destroyed');
    }

    destroyOrder() {
        if (this.order)
            this.order.destroy();
    }

    //Аналогия функции .toString(). Предназначена для получения данных объкта wsUser
    getData() {
        return {
            user: this.user,
            uuid: this.uuid,
            order: this.order ? this.order.getData() : null,
            events: Object.keys(this.events),
            isOnline: this.isOnline,
            type: this.type,
            location: this.location,
            rooms: {
                count: this.rooms.size,
                data: Array.from(this.rooms)
            },
            timeouts: Array.from(this.timeouts.keys()),
            intervals: Array.from(this.intervals.keys()),
        }
    }


    getExecutor() {
        // return {
        //     isOnline: this.isOnline,
        //     socket_id: this.uuid,
        //     user: this.user ? {
        //         id: this.user.id,
        //         type: this.user.type,
        //         name: this.user.name,
        //         phone: this.user.phone,
        //         city_id: this.user.city_id,
        //     } : null,
        // };
        if (!this.user) return null;
        return  {
                    id: this.user.id,
                    type: this.user.type,
                    name: this.user.name,
                    phone: this.user.phone,
                    city_id: this.user.city_id,
                };
    }
    isExecutor() {
        return !!(this.hasUser() && this.user.type === TYPE_EXECUTOR);
    }


    getTechnic(id) {
        try {
            let technics = Array.from(this.user.technics);
            return technics.find(function (technic) {
                return Number(technic.technic_id) === Number(id);
            });
        }
        catch (e) {
            consoleMsg.log(`Cannot get technic: ${e}`);
        }
        return null;
    }


    isUser() {
        return !!(this.hasUser() && this.user.type === TYPE_USER);
    }
    getUser() {
        if (!this.user) return null;
        return  {
            id: this.user.id,
            type: this.user.type,
            name: this.user.name,
            phone: this.user.phone,
            city_id: this.user.city_id,
        };
    }


    hasUser() {
        return !!(this.user && this.user.id);
    }


    setOrder(order) {
        if (order && this.hasUser()) {
            this.order = order;
            if (this.user.type === TYPE_EXECUTOR) {
                order.executor_socket = this;
            }
            else {
                order.user_socket = this;
            }
        }
    }
    hasOrder() {
        return !!(this.order && this.order.uuid);
    }
    async loadOrder(orders) {
        let orderQuery = models.Order.getOrderWithTechnic();


        if (this.isExecutor()) {
            let orderData = await orderQuery.where('executor_id', this.user.id).first();

            if (orderData) {
                let order = functions.setFind(orders, function(order) {
                    return order.data.uuid = orderData.uuid
                });

                if (!order) {
                    order = new Order(orderData);
                    orders.add(order);
                }

                await order.resetUser();
                await order.resetExecutor();

                this.order = order;
                this.order.executor_socket = this;
            }

            return this.order;
        }
        else if (this.isUser()) {
            let orderData = await orderQuery.where('user_id', this.user.id).first();

            if (orderData) {
                let order = functions.setFind(orders, function(order) {
                    return order.data.uuid = orderData.uuid
                });

                if (!order) {
                    order = new Order(orderData);
                    orders.add(order);
                }

                await order.resetUser();
                await order.resetExecutor();

                this.order = order;
                this.order.user_socket = this;
            }

            return this.order;
        }
        return null;
    }

    async hasProcessingOrder() {
        if (this.isExecutor()) {
            let exec_order = await models.Order.query()
                .where('executor_id', this.user.id)
                .where('status', models.Order.IN_PROCESS)
                .first();
            return !!exec_order;
        }
        else if (this.isUser()) {
            let user_order = await models.Order.query()
                .where('user_id', this.user.id)
                .where('status', models.Order.IN_PROCESS)
                .first();
            return !!user_order;
        }
    }

}

module.exports.User = WSUser;
module.exports.TYPE_USER = TYPE_USER;
module.exports.TYPE_EXECUTOR = TYPE_EXECUTOR;
