export default (store) => async (to, from, next) => {

    // Check if the route has any middleware defined
    if (to.meta?.middleware?.length) {
        const middleware = to.meta.middleware

        // Loop through each middleware function
        for (let index = 0; index < middleware.length; index++) {
            const method = middleware[index];

            // CRITICAL: Execute the middleware and await the result.
            const navigationResult = await method({...store, to, from, next})

            // If the middleware explicitly returned a result (meaning it called
            // next() to redirect or halt navigation), we stop the chain
            // and return that result to Vue Router.
            if (navigationResult !== undefined) {
                return navigationResult;
            }
        }

        // If the loop completes without any middleware redirecting,
        // we MUST call next() to finish the navigation.
        return next()
    }

    // Default path: no middleware defined, proceed immediately
    return next()
}