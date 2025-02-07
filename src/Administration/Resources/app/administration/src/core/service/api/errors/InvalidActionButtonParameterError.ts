/**
 * @sw-package framework
 *
 * @private
 */
export default class InvalidActionButtonParameterError extends Error {
    constructor(message: string) {
        super(message);
        this.name = 'InvalidActionButtonParameterError';
    }
}
