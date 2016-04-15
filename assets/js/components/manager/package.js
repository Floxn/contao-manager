'use strict';

const React = require('react');
const _     = require('lodash');

var PackagesComponent = React.createClass({
    getInitialState: function() {
        return {};
    },

    render: function() {
        var stateButton = '';

        if (this.props.canBeEnabled) {
            if (this.props.isEnabled) {
                stateButton = (
                    <button className="disable">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 448"><path className="st0" d="M320 256c0-47.4-25.7-88.7-64-110.9V74.9c74.6 26.4 128 97.5 128 181.1 0 106-86 192-192 192S0 362 0 256c0-83.6 53.4-154.7 128-181.1v70.2C89.7 167.3 64 208.6 64 256c0 70.7 57.3 128 128 128s128-57.3 128-128zm-160-64V32c0-17.7 14.3-32 32-32s32 14.3 32 32v160c0 17.7-14.3 32-32 32s-32-14.3-32-32z"/></svg>
                        Disable
                    </button>
                );
            } else {
                stateButton = (
                    <button className="enable">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 448"><path className="st0" d="M320 256c0-47.4-25.7-88.7-64-110.9V74.9c74.6 26.4 128 97.5 128 181.1 0 106-86 192-192 192S0 362 0 256c0-83.6 53.4-154.7 128-181.1v70.2C89.7 167.3 64 208.6 64 256c0 70.7 57.3 128 128 128s128-57.3 128-128zm-160-64V32c0-17.7 14.3-32 32-32s32 14.3 32 32v160c0 17.7-14.3 32-32 32s-32-14.3-32-32z"/></svg>
                        Enable
                    </button>
                );
            }
        }

        var licenses = [];
        _.forEach(this.props.licenses, function(license) {
            licenses.push(license);
        });

        return (
            <section className="package">
                {this.props.before}

                <div className="inside">
                    <figure><img src="" width="110" height="110" /></figure>

                    <div className="about">
                        <h1>{this.props.name}</h1>
                        <p className="description">{this.props.description} <a href={this.props.website}>More</a></p>
                        <p className="additional">{licenses.join(', ')} &nbsp;&nbsp; | &nbsp;&nbsp; {this.props.installs} Installs</p>
                    </div>

                    <div className="release">
                        <fieldset>
                            <input type="text" value={this.props.constraint} placeholder="latest" disabled />
                            <button><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><path d="M88.1 50c0-1.2-.1-2.4-.2-3.5l12.1-7c-.7-3.4-1.8-6.7-3.2-9.8L82.9 31c-1.2-2-2.7-4-4.3-5.7l5.6-12.5c-2.6-2.3-5.5-4.3-8.5-6.1l-10.5 9.1c-2.2-.9-4.5-1.7-6.9-2.2L55.3.3a47.08 47.08 0 0 0-10.6 0l-3 13.3c-2.4.5-4.7 1.3-6.9 2.2L24.3 6.7c-3.1 1.7-5.9 3.8-8.5 6.1l5.6 12.5c-1.6 1.8-3 3.7-4.3 5.7L3.2 29.7C1.8 32.8.7 36.1 0 39.5l12.1 6.9c-.1 1.2-.2 2.3-.2 3.5s.1 2.4.2 3.5L0 60.5c.7 3.4 1.8 6.7 3.2 9.8L17.1 69c1.2 2 2.7 4 4.3 5.7l-5.6 12.5c2.6 2.3 5.5 4.3 8.5 6.1l10.5-9.1c2.2.9 4.5 1.7 6.9 2.2l3 13.3a47.08 47.08 0 0 0 10.6 0l3-13.3c2.4-.5 4.7-1.3 6.9-2.2l10.5 9.1c3.1-1.7 5.9-3.8 8.5-6.1l-5.6-12.5c1.6-1.8 3-3.7 4.3-5.7l13.9 1.3c1.4-3.1 2.5-6.4 3.2-9.8l-12.1-6.9c.2-1.2.2-2.4.2-3.6zM50 66.4c-9.2 0-16.7-7.3-16.7-16.4 0-9 7.5-16.4 16.7-16.4S66.7 40.9 66.7 50c0 9-7.5 16.4-16.7 16.4z"/></svg></button>
                        </fieldset>
                        <time>2015-08-06<br />09:38 UTC</time>
                    </div>

                    <fieldset className="actions">
                        {stateButton}
                        <button className="uninstall">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 90.8 105.6"><path d="M0 8.3h31.8V0H59v8.3h31.8v12.5H0V8.3zm8.3 17.8h75.8v79.5H8.3m64-70.7h-7.6v62.8h7.5l.1-62.8zm-22.7 0h-7.5v62.8h7.5V34.9zm-22.8.5h-7.5v62.8h7.5V35.4z"/></svg>
                            Remove
                        </button>
                    </fieldset>
                </div>
            </section>
        );
    }
});

module.exports = PackagesComponent;
