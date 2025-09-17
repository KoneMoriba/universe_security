// Neural Network Animation Script
class NeuralNetwork {
    constructor(container) {
        this.container = container;
        this.neurons = [];
        this.connections = [];
        this.init();
    }

    init() {
        this.createNeurons();
        this.createConnections();
        this.animate();
    }

    createNeurons() {
        const neuronCount = 25;
        
        for (let i = 0; i < neuronCount; i++) {
            const neuron = document.createElement('div');
            neuron.className = 'neuron';
            
            // Position aléatoire
            const x = Math.random() * 95 + 2.5; // Éviter les bords
            const y = Math.random() * 95 + 2.5;
            
            neuron.style.left = x + '%';
            neuron.style.top = y + '%';
            neuron.style.animationDelay = Math.random() * 3 + 's';
            
            this.container.appendChild(neuron);
            this.neurons.push({
                element: neuron,
                x: x,
                y: y
            });
        }
    }

    createConnections() {
        for (let i = 0; i < this.neurons.length; i++) {
            for (let j = i + 1; j < this.neurons.length; j++) {
                const neuron1 = this.neurons[i];
                const neuron2 = this.neurons[j];
                
                // Distance entre les neurones
                const distance = Math.sqrt(
                    Math.pow(neuron2.x - neuron1.x, 2) + 
                    Math.pow(neuron2.y - neuron1.y, 2)
                );
                
                // Ne créer une connexion que si les neurones sont proches
                if (distance < 30) {
                    this.createConnection(neuron1, neuron2);
                }
            }
        }
    }

    createConnection(neuron1, neuron2) {
        const connection = document.createElement('div');
        connection.className = 'connection';
        
        // Calculer angle et longueur
        const deltaX = neuron2.x - neuron1.x;
        const deltaY = neuron2.y - neuron1.y;
        const length = Math.sqrt(deltaX * deltaX + deltaY * deltaY);
        const angle = Math.atan2(deltaY, deltaX) * 180 / Math.PI;
        
        connection.style.width = length + '%';
        connection.style.left = neuron1.x + '%';
        connection.style.top = neuron1.y + '%';
        connection.style.transform = `rotate(${angle}deg)`;
        connection.style.transformOrigin = '0 50%';
        connection.style.animationDelay = Math.random() * 4 + 's';
        
        this.container.appendChild(connection);
        this.connections.push(connection);
    }

    animate() {
        // Animation continue des neurones
        setInterval(() => {
            this.neurons.forEach(neuron => {
                // Légère variation de position
                const currentX = parseFloat(neuron.element.style.left);
                const currentY = parseFloat(neuron.element.style.top);
                
                const newX = currentX + (Math.random() - 0.5) * 0.5;
                const newY = currentY + (Math.random() - 0.5) * 0.5;
                
                // Garder dans les limites
                neuron.element.style.left = Math.max(0, Math.min(100, newX)) + '%';
                neuron.element.style.top = Math.max(0, Math.min(100, newY)) + '%';
            });
        }, 5000);
    }
}

// Initialiser l'animation quand le DOM est chargé
document.addEventListener('DOMContentLoaded', function() {
    const neuralContainers = document.querySelectorAll('.neural-bg');
    
    neuralContainers.forEach(container => {
        new NeuralNetwork(container);
    });
});

// Réinitialiser l'animation lors du redimensionnement
window.addEventListener('resize', function() {
    const neuralContainers = document.querySelectorAll('.neural-bg');
    
    neuralContainers.forEach(container => {
        // Nettoyer le contenu existant
        container.innerHTML = '';
        // Recréer l'animation
        new NeuralNetwork(container);
    });
});
