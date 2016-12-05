export class MainController {
    constructor($http, $scope, $log) {
        'ngInject';

        this.log = $log;
        this.scope = $scope;
        this.http = $http;
        this.bulk = {};
        var date = new Date();
        this.getEvents(
            new Date(date.getFullYear(), date.getMonth(), 1).toISOString(),
            new Date(date.getFullYear(), date.getMonth(), 15).toISOString()
        );
    }

    getNextMonth (current) {
        current = new Date(current);
        current.setMonth(current.getMonth() + 1);
        this.getEvents(current.toISOString(), new Date(current.getFullYear(), current.getMonth(), 15).toISOString());
    }

    getEventsFrom (startDate) {
        startDate = new Date(startDate);
        startDate.setDate(startDate.getDate() + 1);
        var endDate = new Date(startDate);
        endDate.setDate(endDate.getDate() + 14);
        this.getEvents(startDate.toISOString(), endDate.toISOString());
    }

    getEvents(startDate, endDate) {
        this.getDateIntervalList(startDate);
        this.log.info(startDate, endDate);
        var url = "/api/calendars?start=" + startDate + "&end=" + endDate;
        this.http.get(url).then(function (response) {
            var days = [],
                inventory = {
                    single: [],
                    double: []
                },
                currentDate = null;
            angular.forEach(response.data, function (value, key) {
                // keys // room
                var dates = Object.keys(value);
                currentDate = dates[0];
                days = dates.map(function (element) {
                    return new Date(element);
                });
                angular.forEach(value, function (value1) {
                    inventory[key].push({available: value1.inventory, price: value1.price, date: value1.date, room: value1.room});
                });
            });
            this.inventory = inventory;
            this.calendarDays = days;
            this.currentDate = currentDate;
        }.bind(this));
    }

    updatePrice (event) {
        var data = {
            price: event.price,
            date: {
                start: event.date.date,
                end: event.date.date
            },
            room: event.room.key
        };
        this.http.post('/api/calendars/price', data).then(function (response) {
            this.log.info(response);
        }.bind(this));
    }

    updateInventory (event) {
        var data = {
            inventory: event.inventory,
            date: {
                start: event.date.date,
                end: event.date.date
            },
            room: event.room.key
        };

        this.http.post('/api/calendars/inventory', data).then(function (response) {
            this.log.info(response);
        }.bind(this));
    }

    updateItemBulk () {
        var data = this.bulk,
            currentDate = new Date(this.currentDate);
        this.bulk = {}; // reset the model.
        this.log.info(data);

        if (angular.isDefined(data.inventory)) {
            this.http.post('/api/calendars/inventory', data).then(function (response) {
                this.log.info(response);
                this.getEvents(
                    currentDate.toISOString(),
                    new Date(currentDate.getFullYear(), currentDate.getMonth(), 15).toISOString()
                );
            }.bind());
        }
        if (angular.isDefined(data.price)) {
            this.http.post('/api/calendars/price', data).then(function (response) {
                this.log.info(response);
                this.getEvents(
                    currentDate.toISOString(),
                    new Date(currentDate.getFullYear(), currentDate.getMonth(), 15).toISOString()
                );
            }.bind(this));
        }
    }

    getDateIntervalList (currentDate) {
        currentDate = new Date(currentDate);
        var months = [],
            endDate = new Date(currentDate);
        endDate.setMonth(endDate.getMonth() + 6);
        while (currentDate < endDate) {
            currentDate.setMonth(currentDate.getMonth() + 1);
            months.push(new Date(currentDate.getFullYear(), currentDate.getMonth(), 1));
        }
        this.scope.months = months;
    }
}
