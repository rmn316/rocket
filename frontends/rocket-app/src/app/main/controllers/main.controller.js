export class MainController {
    constructor(moment, $http, $scope, $log) {
        'ngInject';

        this._moment = moment;
        this.log = $log;
        this.scope = $scope;
        this.http = $http;
        this.bulk = {
        };

        let date = this._getMonthToFrom(new Date());

        this.getEvents(date.start, date.end);

        // this.scope.bulk.start_date = new Date();
        this.scope.beginDatePickerOpen = false;
        // this.scope.bulk.end_date = new Date();
        this.scope.endDatePickerOpen = false;

        this.scope.datePickerOptions = {};
    }

    getNextMonth (current) {

        let currentObj = new Date(current);
        let date = this._getMonthToFrom(new Date(currentObj.getUTCFullYear(), currentObj.getUTCMonth() + 1, 1));
        this.getEvents(date.start, date.end);
    }

    getMoreEvents (lastDisplayedDate) {
        let currentObj = new Date(lastDisplayedDate);
        let date = this._getMonthToFrom(new Date(currentObj.getUTCFullYear(), currentObj.getUTCMonth(), currentObj.getUTCDate() + 1));
        this.log.info("mod", date);
        this.getEvents(date.start, date.end);
    }

    getEventsForMonth(date) {
        let dateObj = this._getMonthToFrom(new Date(date));
        this.getEvents(dateObj.start, dateObj.end);
    }

    getEvents(startDate, endDate) {
        this.log.info("EVT", startDate);
        let startObj = new Date(startDate),
            endObj = new Date(endDate),
            url = "/api/calendars?start=" + startDate.toUTCString() + "&end=" + endDate.toUTCString();
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
                    if (value1.room == null) {
                        value1.room = {
                            key: key
                        };
                    }
                    if (value1.date == null) {
                        value1.date = new Date(value);
                    }
                    inventory[key].push({available: value1.inventory, price: value1.price, date: value1.date, room: value1.room});
                });
            });
            this.inventory = inventory;
            this.getDateIntervalList(startDate.toUTCString()); // to stop by reference
            this.calendarDays = days;
            this.currentDate = startDate;

            this.scope.daysDisplayed = days.length;
        }.bind(this));
    }

    updatePrice (event) {
        let data = {
            price: event.price,
            date: this._parseDate(event.date),
            room: event.room
        };
        this.log.info(data);
        this.http.post('/api/calendars/price', data).then(function (response) {
            this.log.info(response);
        }.bind(this));
    }

    updateInventory (event) {
        let data = {
            inventory: event.available,
            date: this._parseDate(event.date),
            room: event.room
        };
        this.log.info("data", data);

        this.http.post('/api/calendars/inventory', data).then(function (response) {
            this.log.info(response);
        }.bind(this));
    }

    updateItemBulk () {
        let data = this.bulk,
            currentDate = new Date(this.currentDate);

        this.log.info(data);
        // ERROR
        data.start_date = this._parseDate(data.start_date);
        data.end_date = this._parseDate(data.end_date);

        this.bulk = {}; // reset the model.

        this.http.post('/api/calendars', data).then(function (response) {
            this.log.info(response);
            this.getEvents(
                currentDate.toUTCString(),
                new Date(currentDate.getUTCFullYear(), currentDate.getUTCMonth(), 15).toUTCString()
            );
        }.bind(this));
    }

    _parseDate (date) {
        let obj;
        if (angular.isObject(date)) {
            obj = this._moment(date.date);
        } else {
            obj = this._moment(date);
        }

        return obj.format('YYYY-MM-DD');
    }

    getDateIntervalList (currentDate) {
        let date = new Date(currentDate),
            endDate = new Date(date),
            months = [];
        endDate.setUTCMonth(endDate.getUTCMonth() + 6);

        while (date < endDate) {
            date.setUTCMonth(date.getUTCMonth() + 1);
            months.push(new Date(date.getUTCFullYear(), date.getUTCMonth(), 1));
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

        let dateObj = new Date(currentDate);
        let days = this._getDaysInMonth(dateObj.getUTCFullYear(), dateObj.getUTCMonth()),
            halfMonth = Math.ceil(days / 2),
            object = {};

        if (halfMonth > dateObj.getUTCDate()) {
            // get from 1 ...
            object = {
                start: new Date(currentDate.getUTCFullYear(), currentDate.getUTCMonth(), 1),
                end: new Date(currentDate.getFullYear(), currentDate.getMonth(), halfMonth)
            };
        } else {
            object = {
                start: new Date(currentDate.getUTCFullYear(), currentDate.getUTCMonth(), halfMonth + 1),
                end: new Date(currentDate.getUTCFullYear(), currentDate.getUTCMonth(), days)
            }
        }
        return object;
    }

    _dateDiff (startDate, endDate) {
        this.log.info('startdate', startDate, 'enddate', endDate);
        let start = Math.floor( startDate.getTime() / (3600*24*1000)); //days as integer from..
        let end   = Math.floor( endDate.getTime() / (3600*24*1000)); //days as integer from..
        this.log.info("start", start, "end", end, "diff", (end - start));
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
