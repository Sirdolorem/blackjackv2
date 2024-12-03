class DependencyManager {
    static instances = {}; // To hold instances of classes
    static resolvingStack = []; // To prevent circular dependencies

    /**
     * Register a class in the manager
     * @param {string} name - The class name
     * @param {Function} factory - A factory function to create an instance
     */
    static register(name, factory) {
        this.instances[name] = factory;
    }

    /**
     * Get an instance of a class
     * @param {string} name - The class name
     * @returns {Object} - The instance of the class
     * @throws {Error} - Throws error if circular dependency is detected
     */
    static get(name) {
        if (this.resolvingStack.includes(name)) {
            throw new Error(`Circular dependency detected for class: ${name}`);
        }

        if (!this.instances[name]) {
            throw new Error(`Class ${name} not registered.`);
        }

        if (!this.instances[name].instance) {
            this.resolvingStack.push(name);
            this.instances[name].instance = this.instances[name]();
            this.resolvingStack.pop();
        }

        return this.instances[name].instance;
    }
}
