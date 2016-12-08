export class MainController {
    constructor($http, $scope, $log) {
        'ngInject';

        this.log = $log;
        this.scope = $scope;
        this.http = $http;
        this.bulk = {
        };

        let date = this._getMonthToFrom(new Date(Date.now()));

        this.getEvents(date.start, date.end);

        // this.scope.bulk.start_date = new Date();
        this.scope.beginDatePickerOpen = false;
        // this.scope.bulk.end_date = new Date();
        this.scope.endDatePickerOpen = false;

        this.scope.datePickerOptions = {};
    }

    getNextMonth (current) {

        let currentObj = new Date(current);
        let date = this._getMonthToFrom(new Date(currentObj.getFullYear(), currentObj.getMonth() + 1, 1));
        this.getEvents(date.start, date.end);
    }

    getMoreEvents (lastDisplayedDate) {
        let currentObj = new Date(lastDisplayedDate);
        let date = this._getMonthToFrom(new Date(currentObj.getFullYear(), currentObj.getMonth(), currentObj.getDate() + 1));
        this.getEvents(date.start, date.end);
    }

    getEventsForMonth(date) {
        this.log.info(date);
        // let date = this._getMonthToFrom(new Date(date));
        // this.getEvents(date.start, date.end)
    }

    getEvents(startDate, endDate) {
        let url = "/api/calendars?start=" + startDate.toISOString() + "&end=" + endDate.toISOString();
        this.log.info("URL", url);
        this.http.get(url).then(function (response) {
            let days = [],
                inventory = {
                    single: [],
                    double: []
                };
            angular.forEach(response.data, function (value, key) {
                // keys // room
                let dates = Object.keys(value);
                days = dates.map(function (element) {
                    return new Date(element);
                });
                angular.forEach(value, function (value1) {
                    inventory[key].push({available: value1.inventory, price: value1.price, date: value1.date, room: value1.room});
                });
            });
            this.inventory = inventory;
            this.getDateIntervalList(startDate.toISOString()); // to stop by reference
            this.calendarDays = days;
            this.currentDate = startDate;

            this.scope.daysDisplayed = Math.ceil(this._dateDiff(startDate, endDate));
        }.bind(this));
    }

    updatePrice (event) {
        let data = {
            price: event.price,
            start_date: event.date.date,
            end_date: event.date.date,
            room: event.room.key
        };
        this.log.info(data);
        this.http.post('/api/calendars/price', data).then(function (response) {
            this.log.info(response);
        }.bind(this));
    }

    updateInventory (event) {
        this.log.info(event);
        let data = {
            inventory: event.available,
            start_date: event.date.date,
            end_date: event.date.date,
            room: event.room.key
        };
        this.log.info(data);

        this.http.post('/api/calendars/inventory', data).then(function (response) {
            this.log.info(response);
        }.bind(this));
    }

    updateItemBulk () {
        let data = this.bulk,
            currentDate = new Date(this.currentDate);

        data.start_date = new Date(data.start_date);
        data.end_date = new Date(data.end_date);

        if (this.validDates(data.start_date, data.end_date)) {
            this.bulk = {}; // reset the model.

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
        } else {
            // dates not valid.
        }

    }

    getDateIntervalList (currentDate) {
        let date = new Date(currentDate),
            endDate = new Date(date),
            months = [];
        endDate.setMonth(endDate.getMonth() + 6);

        while (date < endDate) {
            date.setMonth(date.getMonth() + 1);
            months.push(new Date(date.getFullYear(), date.getMonth(), 1));
        }
        this.scope.months = months;
    }

    validPrice (value) {
        return !isNaN(parseFloat(value)) && isFinite(value) ? true : value + " is not valid";
    }

    validInventory (value) {
        return !isNaN(parseInt(value)) && isFinite(value) ? true : value + " is not valid";
    }

    _getDaysInMonth (y, m) {
        return(m===2?y&3||!(y%25)&&y&15?28:29:30+(5546>>m&1));
    }

    _getMonthToFrom (currentDate) {

        let days = this._getDaysInMonth(currentDate.getFullYear(), currentDate.getMonth() + 1),
            halfMonth = Math.ceil(days / 2),
            object = {};

        if (halfMonth > currentDate.getDate()) {
            // get from 1 ...
            object = {
                start: new Date(currentDate.getFullYear(), currentDate.getMonth(), 1),
                end: new Date(currentDate.getFullYear(), currentDate.getMonth(), halfMonth)
            };
        } else {
            object = {
                start: new Date(currentDate.getFullYear(), currentDate.getMonth(), halfMonth + 1),
                end: new Date(currentDate.getFullYear(), currentDate.getMonth(), days)
            }
        }
        return object;
    }

    _dateDiff (startDate, endDate) {
        let start = Math.floor( startDate.getTime() / (3600*24*1000)); //days as integer from..
        let end   = Math.floor( endDate.getTime() / (3600*24*1000)); //days as integer from..
        return (end - start) + 1; // exact dates
    }

    openBeginDatePicker () {
        this.scope.beginDatePickerOpen = !this.scope.beginDatePickerOpen;
        this.scope.datePickerOptions.minDate = null;
        if (this.bulk.end_date !== null) {
            this.scope.datePickerOptions.maxDate = new Date(this.bulk.end_date);
        }
    }

    openEndDatePicker () {
        this.scope.endDatePickerOpen = !this.scope.endDatePickerOpen;
        this.scope.datePickerOptions.maxDate = null;
        if (this.bulk.start_date !== null) {
            this.scope.datePickerOptions.minDate = new Date(this.bulk.start_date);
        }
    };
}
