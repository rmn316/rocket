export class MainController {
    constructor(moment, $http, $scope, $log) {
        'ngInject';

        this._moment = moment;
        this.log = $log;
        this.scope = $scope;
        this.http = $http;
        this.bulk = {};

        let date = this._getMonthToFrom(this._moment().utc());

        this.getEvents(date.start, date.end);

        this.scope.beginDatePickerOpen = false;
        this.scope.endDatePickerOpen = false;
        this.scope.datePickerOptions = {};
    }

    getNextMonth (current) {
        let currentObj = this._moment(current).utc().add(1, 'months');
        let date = this._getMonthToFrom(currentObj);
        this.getEvents(date.start, date.end);
    }

    getMoreEvents (lastDisplayedDate) {
        let currentObj = this._moment(lastDisplayedDate).utc().add(1, 'days'),
            date = this._getMonthToFrom(currentObj);
        this.getEvents(date.start, date.end);
    }

    getEventsForMonth(date) {
        let dateObj = this._getMonthToFrom(this._moment(date).utc());
        this.getEvents(dateObj.start, dateObj.end);
    }

    getEvents(startDate, endDate) {
        let url = "/api/calendars?start=" + this._parseDate(startDate) + "&end=" + this._parseDate(endDate);
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
                        value1.date = this._moment(value).utc();
                    } else {
                        value1.date = this._moment(value1.date.date).utc();
                    }
                    inventory[key].push({available: value1.inventory, price: value1.price, date: value1.date, room: value1.room});
                }.bind(this));
            }.bind(this));
            this.currentDate = this._moment(startDate).utc();
            this.inventory = inventory;
            this.getDateIntervalList(this.currentDate.clone()); // to stop by reference
            this.calendarDays = days;

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
        let date,
            data = this.bulk,
            currentDate = this._moment(this.currentDate).utc();
        // convert keys to array...
        data.days = angular.isArray(this.bulk.days) ? Object.keys(this.bulk.days) : [];

        this.log.info(this.bulk);

        this.http.post('/api/calendars/bulk', data).then(function (response) {
            this.log.info(response);
            date = this._getMonthToFrom(currentDate);
            this.log.info(date);
            this.getEvents(date.start, date.end);
            this.bulk = {}; // reset the model.
        }.bind(this));
    }

    getDateIntervalList (currentDate) {
        let date = currentDate,
            endDate = this._moment(currentDate).utc().add(6, 'months'),
            months = [];

        while (date <= endDate) {
            date.add(1, 'months');
            months.push(date);
            date = date.clone();
        }
        this.scope.months = months;
    }

    validPrice (value) {
        return !isNaN(parseFloat(value)) && isFinite(value) ? true : value + " is not valid";
    }

    validInventory (value) {
        return !isNaN(parseInt(value)) && isFinite(value) ? true : value + " is not valid";
    }

    _parseDate (date) {
        let obj = date;
        if (!date._isAMomentObject && angular.isDefined(date.date)) {
            obj = this._moment(date.date);
        } else if (!date._isAMomentObject) {
            obj = this._moment(date);
        }

        return obj.format('YYYY-MM-DD');
    }

    _getDaysInMonth (date) {
        return date.add('months', 1).date(1).subtract('days', 1).format('DD');
    }

    _getMonthToFrom (currentDate) {
        let dateObj = currentDate;
        let days = this._getDaysInMonth(this._moment(dateObj).utc()),
            halfMonth = Math.ceil(days / 2),
            object = {};

        if (halfMonth > dateObj.get('date')) {
            // get from 1 ...
            object = {
                start: this._moment(dateObj).utc().startOf('month'),
                end: this._moment(dateObj).utc().date(halfMonth)
            };
        } else {
            object = {
                start: this._moment(dateObj).utc().date(halfMonth+1),
                end: this._moment(dateObj).utc().date(days)
            }
        }
        return object;
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
    }
}
